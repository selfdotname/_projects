import express from "express";
import session from "express-session";

import { mysqlRouter } from "./Routes/mysqlRoutes.js";

const PORT = 3000;

const app = express();

app.use(
  session({
    secret: "my-secret",
    resave: false,
    saveUninitialized: true,
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
