const express = require('express');
const router = express.Router();
const User = require('../models/User'); // مدل mongoose

// گرفتن همه کاربران
router.get('/', async (req, res) => {
  const users = await User.find();
  res.json(users);
});

// افزودن کاربر جدید
router.post('/', async (req, res) => {
  const newUser = new User(req.body);
  await newUser.save();
  res.json(newUser);
});

// حذف کاربر
router.delete('/:id', async (req, res) => {
  await User.findByIdAndDelete(req.params.id);
  res.json({ success: true });
});

module.exports = router;
