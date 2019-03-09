const mysql = require('mysql2/promise');
const config = require('config');
const dbConfig = config.get('database');

(async () => {
    let connection = null;
    try {
        // Load the required module to import
        const args = process.argv.slice(2);
        if (args.length !== 1) {
            console.log('usage: node import.js <importer_name>');
            process.exit(1);
        }
        const importerName = args[0];

        const importer = require('./importers/' + importerName + '.imp');

        // Create a connection
        connection = await mysql.createConnection({
            host: dbConfig.host,
            user: dbConfig.user,
            database: dbConfig.name,
            password: dbConfig.password
        });

        await connection.query('START TRANSACTION');

        await importer.import(connection);

        await connection.query('COMMIT');
    } catch (err) {
        await connection.query('ROLLBACK');
        console.log(err.message);
    } finally {
        if (connection) {
            connection.end();
        }
    }
})();
