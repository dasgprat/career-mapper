const fs = require('fs');
const util = require('util');
const path = require('path');

const _readFile = util.promisify(fs.readFile);

module.exports = {
    import: async connection => {
        try {
            let rawData = await _readFile(path.join(__dirname, '..', 'data', 'indeed.tsv'), 'utf8');
            let rawDataLines = rawData.split('\n');

            let head = true;

            for (var line of rawDataLines) {
                if (head) {
                    head = false;
                } else {
                    let data = extractData(line);
                    if (data) {
                        console.log(data);
                        // TODO: import cleaned data into database
                    }
                }
            }
        } catch (err) {
            throw new Error('Failed to import data: ' + err.message);
        }
    }
};

const hoursWorkedPerYear = 1811;

function extractData(line) {
    let parts = line.split('\t');
    let title = parts[0];
    let profession = parts[1];
    let company = parts[2];
    let locationParts = parts[3].split(',');
    // Some of the entries only have the country. We don't want this data, as most of it doesn't have
    // salary data anyways
    if (locationParts.length < 2) {
        return null;
    }
    let city = locationParts[0].trim();
    let stateParts = locationParts[1].trim().split(' ');
    let state = stateParts[0];
    let salaryMin = parts[4] !== '' ? parseInt(parts[4]) : null;
    let salaryMax = parts[5] !== '' ? parseInt(parts[5]) : null;
    // We don't want data that is missing salary information...
    if(salaryMin === null || salaryMax === null) {
        return null;
    }
    if(salaryMin < 100) salaryMin *= hoursWorkedPerYear;
    if(salaryMax < 100) salaryMax *= hoursWorkedPerYear;

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
