import * as SQLite from "expo-sqlite"

const db = SQLite.openDatabase("soilAnalyzer.db")

// Initialize database with tables
export const initDatabase = () => {
  return new Promise((resolve, reject) => {
    db.transaction(
      (tx) => {
        // Create states table
        tx.executeSql(
          `CREATE TABLE IF NOT EXISTS states (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          name TEXT NOT NULL
        );`,
        )

        // Create districts table
        tx.executeSql(
          `CREATE TABLE IF NOT EXISTS districts (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          state_id INTEGER NOT NULL,
          name TEXT NOT NULL,
          FOREIGN KEY (state_id) REFERENCES states(id)
        );`,
        )

        // Create crops table
        tx.executeSql(
          `CREATE TABLE IF NOT EXISTS crops (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          name TEXT NOT NULL
        );`,
        )

        // Create crop requirements table
        tx.executeSql(
          `CREATE TABLE IF NOT EXISTS crop_requirements (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          crop_id INTEGER NOT NULL,
          state_id INTEGER NOT NULL,
          district_id INTEGER NOT NULL,
          required_n REAL NOT NULL,
          required_p REAL NOT NULL,
          required_k REAL NOT NULL,
          min_ph REAL NOT NULL,
          max_ph REAL NOT NULL,
          FOREIGN KEY (crop_id) REFERENCES crops(id),
          FOREIGN KEY (state_id) REFERENCES states(id),
          FOREIGN KEY (district_id) REFERENCES districts(id)
        );`,
        )

        // Create soil analyses table
        tx.executeSql(
          `CREATE TABLE IF NOT EXISTS soil_analyses (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          nitrogen REAL NOT NULL,
          phosphorus REAL NOT NULL,
          potassium REAL NOT NULL,
          ph REAL NOT NULL,
          plot_area REAL NOT NULL,
          area_unit TEXT NOT NULL,
          state_id INTEGER NOT NULL,
          district_id INTEGER NOT NULL,
          crop_id INTEGER NOT NULL,
          results TEXT,
          date TEXT NOT NULL,
          FOREIGN KEY (state_id) REFERENCES states(id),
          FOREIGN KEY (district_id) REFERENCES districts(id),
          FOREIGN KEY (crop_id) REFERENCES crops(id)
        );`,
        )

        // Insert sample data
        insertSampleData(tx)
      },
      (error) => reject(error),
      () => resolve(),
    )
  })
}

const insertSampleData = (tx) => {
  // Insert states
  const states = [
    "Punjab",
    "Bihar",
    "Maharashtra",
    "Gujarat",
    "Uttar Pradesh",
    "Rajasthan",
    "Madhya Pradesh",
    "Karnataka",
    "Tamil Nadu",
    "Andhra Pradesh",
  ]

  states.forEach((state, index) => {
    tx.executeSql("INSERT OR IGNORE INTO states (id, name) VALUES (?, ?)", [index + 1, state])
  })

  // Insert districts
  const districts = [
    { stateId: 1, name: "Ludhiana" },
    { stateId: 1, name: "Amritsar" },
    { stateId: 1, name: "Patiala" },
    { stateId: 2, name: "Patna" },
    { stateId: 2, name: "Gaya" },
    { stateId: 2, name: "Muzaffarpur" },
    { stateId: 3, name: "Pune" },
    { stateId: 3, name: "Mumbai" },
    { stateId: 3, name: "Nagpur" },
    { stateId: 4, name: "Surat" },
    { stateId: 4, name: "Ahmedabad" },
    { stateId: 4, name: "Vadodara" },
  ]

  districts.forEach((district, index) => {
    tx.executeSql("INSERT OR IGNORE INTO districts (id, state_id, name) VALUES (?, ?, ?)", [
      index + 1,
      district.stateId,
      district.name,
    ])
  })

  // Insert crops
  const crops = ["Wheat", "Rice", "Maize", "Cotton", "Sugarcane", "Soybean", "Groundnut", "Potato", "Tomato", "Onion"]

  crops.forEach((crop, index) => {
    tx.executeSql("INSERT OR IGNORE INTO crops (id, name) VALUES (?, ?)", [index + 1, crop])
  })

  // Insert crop requirements
  const cropRequirements = [
    { cropId: 1, stateId: 1, districtId: 1, requiredN: 120, requiredP: 60, requiredK: 40, minPh: 6.0, maxPh: 7.5 },
    { cropId: 1, stateId: 2, districtId: 4, requiredN: 110, requiredP: 55, requiredK: 35, minPh: 6.0, maxPh: 7.5 },
    { cropId: 2, stateId: 1, districtId: 1, requiredN: 100, requiredP: 50, requiredK: 35, minPh: 5.5, maxPh: 7.0 },
    { cropId: 2, stateId: 2, districtId: 4, requiredN: 90, requiredP: 45, requiredK: 30, minPh: 5.5, maxPh: 7.0 },
    { cropId: 3, stateId: 3, districtId: 7, requiredN: 150, requiredP: 70, requiredK: 50, minPh: 5.8, maxPh: 7.2 },
    { cropId: 4, stateId: 4, districtId: 10, requiredN: 160, requiredP: 80, requiredK: 60, minPh: 6.2, maxPh: 7.8 },
  ]

  cropRequirements.forEach((req, index) => {
    tx.executeSql(
      "INSERT OR IGNORE INTO crop_requirements (id, crop_id, state_id, district_id, required_n, required_p, required_k, min_ph, max_ph) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
      [
        index + 1,
        req.cropId,
        req.stateId,
        req.districtId,
        req.requiredN,
        req.requiredP,
        req.requiredK,
        req.minPh,
        req.maxPh,
      ],
    )
  })
}

// Get all states
export const getStates = () => {
  return new Promise((resolve, reject) => {
    db.transaction((tx) => {
      tx.executeSql(
        "SELECT * FROM states ORDER BY name",
        [],
        (_, { rows }) => resolve(rows._array),
        (_, error) => reject(error),
      )
    })
  })
}

// Get districts by state ID
export const getDistricts = (stateId) => {
  return new Promise((resolve, reject) => {
    db.transaction((tx) => {
      tx.executeSql(
        "SELECT * FROM districts WHERE state_id = ? ORDER BY name",
        [stateId],
        (_, { rows }) => resolve(rows._array),
        (_, error) => reject(error),
      )
    })
  })
}

// Get all crops
export const getCrops = () => {
  return new Promise((resolve, reject) => {
    db.transaction((tx) => {
      tx.executeSql(
        "SELECT * FROM crops ORDER BY name",
        [],
        (_, { rows }) => resolve(rows._array),
        (_, error) => reject(error),
      )
    })
  })
}

// Get crop requirements
export const getCropRequirements = (cropId, stateId, districtId) => {
  return new Promise((resolve, reject) => {
    db.transaction((tx) => {
      tx.executeSql(
        "SELECT * FROM crop_requirements WHERE crop_id = ? AND state_id = ? AND district_id = ?",
        [cropId, stateId, districtId],
        (_, { rows }) => {
          if (rows.length > 0) {
            resolve(rows._array[0])
          } else {
            // Fallback to state level
            tx.executeSql(
              "SELECT * FROM crop_requirements WHERE crop_id = ? AND state_id = ? LIMIT 1",
              [cropId, stateId],
              (_, { rows }) => {
                if (rows.length > 0) {
                  resolve(rows._array[0])
                } else {
                  // Default values
                  resolve({
                    required_n: 120,
                    required_p: 60,
                    required_k: 40,
                    min_ph: 6.0,
                    max_ph: 7.5,
                  })
                }
              },
              (_, error) => reject(error),
            )
          }
        },
        (_, error) => reject(error),
      )
    })
  })
}

// Save soil analysis
export const saveSoilAnalysis = (analysisData) => {
  return new Promise((resolve, reject) => {
    db.transaction((tx) => {
      tx.executeSql(
        `INSERT INTO soil_analyses 
         (nitrogen, phosphorus, potassium, ph, plot_area, area_unit, state_id, district_id, crop_id, results, date) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          Number.parseFloat(analysisData.nitrogen),
          Number.parseFloat(analysisData.phosphorus),
          Number.parseFloat(analysisData.potassium),
          Number.parseFloat(analysisData.ph),
          Number.parseFloat(analysisData.plotArea),
          analysisData.areaUnit,
          Number.parseInt(analysisData.state),
          Number.parseInt(analysisData.district),
          Number.parseInt(analysisData.crop),
          JSON.stringify(analysisData.results),
          analysisData.date,
        ],
        (_, result) => resolve(result.insertId),
        (_, error) => reject(error),
      )
    })
  })
}

// Get saved analyses
export const getSavedAnalyses = () => {
  return new Promise((resolve, reject) => {
    db.transaction((tx) => {
      tx.executeSql(
        `SELECT sa.*, s.name as state_name, d.name as district_name, c.name as crop_name
         FROM soil_analyses sa
         JOIN states s ON sa.state_id = s.id
         JOIN districts d ON sa.district_id = d.id
         JOIN crops c ON sa.crop_id = c.id
         ORDER BY sa.date DESC`,
        [],
        (_, { rows }) => {
          const analyses = rows._array.map((row) => ({
            id: row.id,
            nitrogen: row.nitrogen,
            phosphorus: row.phosphorus,
            potassium: row.potassium,
            ph: row.ph,
            plotArea: row.plot_area,
            areaUnit: row.area_unit,
            state: row.state_name,
            district: row.district_name,
            crop: row.crop_name,
            date: row.date,
            results: JSON.parse(row.results || "{}"),
          }))
          resolve(analyses)
        },
        (_, error) => reject(error),
      )
    })
  })
}

// Delete soil analysis
export const deleteSoilAnalysis = (id) => {
  return new Promise((resolve, reject) => {
    db.transaction((tx) => {
      tx.executeSql(
        "DELETE FROM soil_analyses WHERE id = ?",
        [id],
        (_, result) => resolve(result),
        (_, error) => reject(error),
      )
    })
  })
}
