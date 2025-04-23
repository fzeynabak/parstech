#!/bin/bash

# Create main project directory
mkdir -p parstech

# Create assets directory and its subdirectories
mkdir -p parstech/assets/css
mkdir -p parstech/assets/js
mkdir -p parstech/assets/vendor

# Create config directory
mkdir -p parstech/config

# Create includes directory
mkdir -p parstech/includes

# Create modules directory and its subdirectories
mkdir -p parstech/modules/{dashboard,persons,products,banking,sales,purchases,inventory,accounting,reports}

# Create api directory and its subdirectories
mkdir -p parstech/api/ajax

# Create CSS files
touch parstech/assets/css/style.css
touch parstech/assets/css/sidebar.css

# Create JavaScript files
touch parstech/assets/js/main.js
touch parstech/assets/js/sidebar.js

# Create PHP files
touch parstech/assets/vendor/config.php
touch parstech/config/database.php
touch parstech/config/constants.php
touch parstech/includes/auth.php
touch parstech/includes/functions.php
touch parstech/includes/header.php
touch parstech/index.php

# Set appropriate permissions
chmod 755 parstech/
find parstech/ -type d -exec chmod 755 {} \;
find parstech/ -type f -exec chmod 644 {} \;

echo "Project structure has been created successfully!"