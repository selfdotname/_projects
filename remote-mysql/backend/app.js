import express from "express";
import session from "express-session";
import MySQLStore from "express-mysql-session";

import { mysqlRouter } from "./Routes/mysqlRoutes.js";

const options = {
  host: "sql7.freesqldatabase.com",
  user: "sql7783543",
  password: "qY9JuD3Z7w",
  database: "sql7783543",
};
const PORT = 3000;

const app = express();
const sessionStore = new MySQLStore(options);

app.use(
  session({
    secret: "my-secret",
    resave: false,
    saveUninitialized: true,
    store: sessionStore
  })
);

app.use("/mysql", mysqlRouter);

app.get("/set-session", (req, res) => {
  req.session.name = "DEVOPS";
  res.send("session set");
});

app.get("/get-session", (req, res) => {
  res.send(req.session.name);
});

app.listen(PORT, () => console.log(`http://localhost:${PORT}`));
