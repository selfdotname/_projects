import mysql2 from "mysql2";

export const pool = mysql2
  .createPool({
    host: "sql7.freesqldatabase.com",
    user: "sql7783543",
    password: "qY9JuD3Z7w",
    database: "sql7783543",
  })
  .promise();