const fs = require('fs');
const util = require('util');
const path = require('path');

const _readFile = util.promisify(fs.readFile);

module.exports = {
    import: async connection => {
        try {
            let rawData = await _readFile(path.join(__dirname, '..', 'data', 'index.csv'), 'utf8');
            let rawDataLines = rawData.split('\n');
            for (var line of rawDataLines) {
                var data = extractIndexData(line);
                if (data) {
                    // First, make sure the location exists. If not, create it
                    let params = [data.city, data.state];
                    let [res, fields] = await connection.query(
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

                    // Once we have the id of the location, create a new index entry
                    params = [
                        lid,
                        data.safety,
                        data.health,
                        data.crime,
                        data.traffic,
                        data.pollution,
                        data.qualityOfLife,
                        data.groceries,
                        data.rent
                    ];
                    let query = 'INSERT INTO cm_index ';
                    query += '(i_location, i_safety, i_health, i_crime, i_traffic, i_pollution, i_quality_of_life, ';
                    query += 'i_groceries, i_rent) ';
                    query += 'VALUES(?,?,?,?,?,?,?,?,?)';
                    [res, fields] = await connection.query(query, params);
                }
            }
        } catch (err) {
            throw new Error('Failed to import data: ' + err.message);
        }
    }
};

function extractIndexData(line) {
    let parts = line.split(',');
    if (parts[8].length > 2 || parts[8] === '' || parts[7] === '') {
        // We only want US locations (state code of length 2)
        // We also don't want the location to be empty
        return null;
    }
    return {
        health: parts[0] !== '' ? parseFloat(parts[0]) : null,
        crime: parts[1] !== '' ? parseFloat(parts[1]) : null,
        pollution: parts[2] !== '' ? parseFloat(parts[2]) : null,
        traffic: parts[3] !== '' ? parseFloat(parts[3]) : null,
        qualityOfLife: parts[4] !== '' ? parseFloat(parts[4]) : null,
        groceries: parts[5] !== '' ? parseFloat(parts[5]) : null,
        safety: parts[6] !== '' ? parseFloat(parts[6]) : null,
        city: parts[7],
        state: parts[8],
        rent: parts[9] !== '' ? parseFloat(parts[9]) : null
    };
}
