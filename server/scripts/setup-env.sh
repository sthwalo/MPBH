#!/bin/bash

# Copy the example environment file to create a new .env file
cp .env.example .env

# Generate and append a random database password
echo "DB_PASSWORD=$(openssl rand -base64 12)" >> .env

# Generate and append a random session secret
echo "SESSION_SECRET=$(openssl rand -hex 32)" >> .env

# Generate random CSRF secret
echo "CSRF_SECRET=$(openssl rand -hex 24)" >> .env

# Generate random JWT secret if not already set
sed -i '' 's/JWT_SECRET=change_this_to_a_secure_random_string/JWT_SECRET='"$(openssl rand -hex 32)"'/g' .env

echo "Environment configuration completed successfully."
echo "Make sure to update the database credentials with your actual values."
