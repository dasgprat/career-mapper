const fs = require('fs');
const readline = require('readline');
const path = require('path');

let start = 10000;
let stop = 161486;

module.exports = {
    import: connection =>
        new Promise((resolve, reject) => {
            console.log('Importing BLS data');
            let file = path.join(__dirname, '..', 'data', 'citywise.csv');
            let head = true;
            let total = 0;
            let count = 0;
            let interface = readline.createInterface({
                input: fs.createReadStream(file),
                console: false
            });

            interface.on('line', line => {
                if (head) return (head = false);

                let data = extractData(line);

                if (data) {
                    total++;
                    if (total > start && total <= stop) {
                        getLocationId(connection, data.city, data.state, reject).then(lid => {
                            getProfessionId(connection, data.profession, reject).then(pid => {
                                insertJob(
                                    connection,
                                    {
                                        title: data.title,
                                        pid,
                                        salaryMin: data.salaryMin,
                                        salaryMax: data.salaryMax,
                                        lid
                                    },
                                    reject
                                ).then(() => {
                                    count++;
                                    if (count === stop - start) {
                                        console.log('Finished: ' + count);
                                        resolve();
                                    }
                                });
                            });
                        });
                    }
                }
            });

            interface.on('close', () => {
                console.log('Closing stream to ' + file);
                console.log('Importing ' + (stop - start) + ' records');
            });

            interface.on('error', err => {
                reject(new Error('Failed to import data: ' + err.message));
            });
        })
};

function extractData(line) {
    let parts = line.match(/(".*?"|[^",\s]+)(?=\s*,|\s*$)/g).map(p => p.replace(/\"/g, '')) || [];
    let locationParts = parts[1].split(',');
    if (locationParts.length < 2) return null;
    let city = locationParts[0].trim().split('-')[0];
    let state = locationParts[1]
        .trim()
        .split(' ')[0]
        .split('-')[0]
        .trim();

    // The state isn't valid. Throw out data
    if (!state || state.length > 2) {
        return null;
    }

    let profession = parts[4].toLowerCase().trim();
    let title = parts[7].toLowerCase().trim();

    let salary = parseInt(parts[14].replace(/\,/g, '').trim());
    if (!salary) return null;

    let salaryMin = salary;
    let salaryMax = salary;

    return {
        title,
        profession,
        city,
        state,
        salaryMin,
        salaryMax
    };
}

async function getLocationId(connection, city, state, reject) {
    try {
        let params = [city, state];
        [res, fields] = await connection.query('SELECT lid FROM cm_location WHERE l_city = ? AND l_state = ?', params);
        let lid = null;
        if (res.length == 0) {
            [res, fields] = await connection.query('INSERT INTO cm_location (l_city, l_state) VALUES (?,?)', params);
            lid = res.insertId;
        } else {
            lid = res[0].lid;
        }
        return lid;
    } catch (err) {
        reject(new Error('Failed to get location ID: ' + err.message));
    }
}

async function getProfessionId(connection, profession, reject) {
    try {
        // Get the profession ID. If the profession isn't in the table, add it
        params = [profession];
        [res, fields] = await connection.query('SELECT pid FROM cm_profession WHERE p_name = ?', params);
        let pid = null;
        if (res.length === 0) {
            [res, fields] = await connection.query('INSERT INTO cm_profession (p_name) VALUES (?)', params);
            pid = res.insertId;
        } else {
            pid = res[0].pid;
        }
        return pid;
    } catch (err) {
        reject(new Error('Failed to get profession ID: ' + err.message));
    }
}

async function insertJob(connection, { title, pid, salaryMin, salaryMax, lid }, reject) {
    try {
        // Insert a new entry in the cm_job table
        let noCompanyCid = 4323;
        params = [title, pid, salaryMin, salaryMax, lid, noCompanyCid];
        let sql = 'INSERT INTO cm_job ';
        sql += '(j_title, j_profession, j_salary_min, j_salary_max, j_location, j_company) ';
        sql += 'VALUES(?,?,?,?,?,?)';
        [res, params] = await connection.query(sql, params);
    } catch (err) {
        reject(new Error('Failed to insert new job in database: ' + err.message));
    }
}
