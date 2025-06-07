import express from "express";
import { pool } from "../database.js";

export const mysqlRouter = express.Router();

mysqlRouter.get("/get-users", async (req, res) => {
  try {
    const [rows] = await pool.query("SELECT * FROM people");
    res.send(rows);
  } catch (err) {
    res.send(err.message);
  }
});

mysqlRouter.get("/version", async (req, res) => {
  try {
    const [rows] = await pool.query("SELECT VERSION()");
    res.send(rows);
  } catch (err) {
    res.send(err.message);
  }
});

mysqlRouter.get("/create-table", async (req, res) => {
  try {
    const result = await pool.query(`
      CREATE TABLE people (
        id INT PRIMARY KEY AUTO_INCREMENT,
        fname TINYTEXT,
        lname TINYTEXT
      )
    `);
    res.send(result);
  } catch (err) {
    res.send(err.message);
  }
});

mysqlRouter.get("/insert-users", async (req, res) => {
  try {
    const result = await pool.query(`
      INSERT INTO people (fname, lname)
      VALUES 
      ('John', 'Doe'),
      ('Bob', 'Brown')
    `);
    res.send(result);
  } catch (err) {
    res.send(err.message);
  }
});
