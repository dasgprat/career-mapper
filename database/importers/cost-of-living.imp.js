const fs = require('fs');
const util = require('util');
const path = require('path');

const _readFile = util.promisify(fs.readFile);

module.exports = {
    import: async connection => {
        try {
            let rawData = await _readFile(path.join(__dirname, '..', 'data', 'cost-of-living.tsv'), 'utf8');
            let rawDataLines = rawData.split('\n');

            let head = true;

            for (var line of rawDataLines) {
                if (head) {
                    head = false;
                } else {
                    let data = extractData(line);
                    if (data) {
                        console.log('Importing...');
                        console.log(data);
                        [res, fields] = await connection.query('SELECT lid FROM cm_location WHERE l_state = ?', [
                            data.state
                        ]);
                        for (var { lid } of res) {
                            let sql = 'INSERT INTO cm_living_cost ';
                            sql += '(lc_location, lc_average_cost, lc_housing_cost) ';
                            sql += 'VALUES(?,?,?)';
                            let params = [lid, data.averageLivingCost, data.averageHouseValue];
                            [res, fields] = await connection.query(sql, params);
                        }
                    }
                }
            }
        } catch (err) {
            throw new Error('Failed to import data: ' + err.message);
        }
    }
};

function extractData(line) {
    let parts = line.split('\t');
    let state = parts[1].trim();
    let averageLivingCost = parseInt(parts[2].trim());
    let averageHouseValue = parseInt(parts[3].trim());
    return {
        state,
        averageHouseValue,
        averageLivingCost
    };
}
