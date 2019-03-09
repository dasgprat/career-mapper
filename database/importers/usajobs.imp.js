const fs = require('fs');
const util = require('util');
const path = require('path');

const _readFile = util.promisify(fs.readFile);

module.exports = {
    import: async connection => {
        try {
            let rawData = await _readFile(path.join(__dirname, '..', 'data', 'usajobs.tsv'), 'utf8');
            let stateMappingsText = await _readFile(path.join(__dirname, '..', 'data', 'state-mappings.txt'), 'utf8');
            let stateMappingsArray = stateMappingsText.split('\n');
            const stateMappings = {};
            stateMappingsArray.forEach(line => {
                let [long, short] = line.split(',');
                stateMappings[long] = short;
            });
            let rawDataLines = rawData.split('\n');

            let head = true;

            for (var line of rawDataLines) {
                if (head) {
                    head = false;
                } else {
                    let data = extractData(line, stateMappings);
                    if (data) {
                        // Get the company ID. If the company isn't in the table, add it
                        let params = [data.company];
                        let [res, fields] = await connection.query(
                            'SELECT cid FROM cm_company WHERE c_name = ?',
                            params
                        );
                        let cid = null;
                        if (res.length === 0) {
                            [res, fields] = await connection.query(
                                'INSERT INTO cm_company (c_name) VALUES (?)',
                                params
                            );
                            cid = res.insertId;
                        } else {
                            cid = res[0].cid;
                        }

                        // Get the location ID. If the location isn't in the table, add it
                        params = [data.city, data.state];
                        [res, fields] = await connection.query(
                            'SELECT lid FROM cm_location WHERE l_city = ? AND l_state = ?',
                            params
                        );
                        let lid = null;
                        if (res.length === 0) {
                            [res, fields] = await connection.query(
                                'INSERT INTO cm_location (l_city, l_state) VALUES (?,?)',
                                params
                            );
                            lid = res.insertId;
                        } else {
                            lid = res[0].lid;
                        }

                        // Get the profession ID. If the profession isn't in the table, add it
                        params = [data.profession];
                        [res, fields] = await connection.query(
                            'SELECT pid FROM cm_profession WHERE p_name = ?',
                            params
                        );
                        let pid = null;
                        if (res.length === 0) {
                            [res, fields] = await connection.query(
                                'INSERT INTO cm_profession (p_name) VALUES (?)',
                                params
                            );
                            pid = res.insertId;
                        } else {
                            pid = res[0].pid;
                        }

                        // Insert a new entry in the cm_job table
                        params = [data.title, pid, data.salaryMin, data.salaryMax, cid, lid];
                        let sql = 'INSERT INTO cm_job ';
                        sql += '(j_title, j_profession, j_salary_min, j_salary_max, j_company, j_location) ';
                        sql += 'VALUES(?,?,?,?,?,?)';
                        [res, params] = await connection.query(sql, params);
                    }
                }
            }
        } catch (err) {
            throw new Error('Failed to import data: ' + err.message);
        }
    }
};

const hoursWorkedPerYear = 1811;

function extractData(line, stateMappings) {
    let parts = line.split('\t');
    let title = parts[0].toLowerCase();
    let profession = parts[1].toLowerCase().trim();
    let locationParts = parts[2].split(',');
    let city = locationParts[0].trim();
    let state = stateMappings[locationParts[1].trim()];
    // The state isn't valid, meaning it didn't map to a code correctly. Throw out
    // the dataset
    if (!state) {
        return null;
    }
    let salaryMin = parts[3] !== '' ? parseFloat(parts[3]) : null;
    let salaryMax = parts[4] !== '' ? parseFloat(parts[4]) : null;
    // We don't want data that is missing salary information...
    if (salaryMin === null || salaryMax === null) {
        return null;
    }
    if (salaryMin < 100) salaryMin *= hoursWorkedPerYear;
    if (salaryMax < 100) salaryMax *= hoursWorkedPerYear;
    salaryMin = Math.floor(salaryMin);
    salaryMax = Math.floor(salaryMax);
    let company = parts[6].replace(/[\n\r]+/g, '');

    return {
        title,
        profession,
        company,
        city,
        state,
        salaryMin,
        salaryMax
    };
}
