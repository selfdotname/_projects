import express from "express";
import multer from "multer";
import dotenv from "dotenv";
import path from "path";

dotenv.config();

const app = express();
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, "uploads/");
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    const name = path.basename(file.originalname, ext);
    cb(null, name + "-" + Date.now() + ext);
  },
});
const upload = multer({ storage });

app.get("/", (req, res) => {
  res.send(
    '<form action="/upload" method="POST" enctype="multipart/form-data"><input type="file" name="file" /><button>Submit</button></form>'
  );
});

app.post("/upload", upload.single("file"), (req, res) => {
  console.log(req.file);
  res.redirect("/");
});

app.listen(process.env.PORT, () =>
  console.log(`http://localhost:${process.env.PORT}`)
);
