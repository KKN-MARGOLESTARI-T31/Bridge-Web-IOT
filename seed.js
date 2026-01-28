require('dotenv').config();
const { Client } = require('pg');

const client = new Client({
    connectionString: process.env.DATABASE_URL,
    ssl: {
        rejectUnauthorized: false
    }
});

const fs = require('fs');
const path = require('path');

async function seed() {
    console.log('Connecting to Neon DB...');
    try {
        await client.connect();
        console.log('Connected successfully!');

        // Read and execute table creation SQL
        console.log('Checking/Creating table structure...');
        const sqlPath = path.join(__dirname, 'create_monitoring_logs_table.sql');
        const sql = fs.readFileSync(sqlPath, 'utf8');
        await client.query(sql);
        console.log('Table structure ensured.');

        const locations = ['sawah', 'sumur', 'kolam'];
        const totalData = 10;

        console.log(`Seeding ${totalData} dummy records...`);

        for (let i = 0; i < totalData; i++) {
            const ph = (Math.random() * (8.5 - 6.0) + 6.0).toFixed(2);
            const battery = (Math.random() * (100 - 20) + 20).toFixed(2);
            const location = locations[Math.floor(Math.random() * locations.length)];

            // Random time within last 7 days
            const daysAgo = Math.floor(Math.random() * 7);
            const hoursAgo = Math.floor(Math.random() * 24);

            const timestamp = new Date();
            timestamp.setDate(timestamp.getDate() - daysAgo);
            timestamp.setHours(timestamp.getHours() - hoursAgo);

            const query = `
            INSERT INTO monitoring_logs (ph_value, battery_level, location, created_at) 
            VALUES ($1, $2, $3, $4)
        `;

            await client.query(query, [ph, battery, location, timestamp.toISOString()]);
            console.log(`[${i + 1}/${totalData}] Inserted: pH=${ph}, Bat=${battery}, Loc=${location}`);
        }

        console.log('\nSeeding completed successfully!');

        // Verify count
        const res = await client.query('SELECT COUNT(*) FROM monitoring_logs');
        console.log(`Total rows in table: ${res.rows[0].count}`);

    } catch (err) {
        console.error('Connection/Seeding Error:', err.message);
    } finally {
        await client.end();
    }
}

seed();
