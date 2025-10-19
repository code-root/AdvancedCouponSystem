#!/bin/bash

# Advanced Coupon System - Optimized Build Script
# This script provides a fast and efficient build process

echo "🚀 Starting Advanced Coupon System Build Process..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    print_error "composer.json not found. Please run this script from the project root."
    exit 1
fi

# Set environment variables for optimal performance
export NODE_OPTIONS="--max-old-space-size=2048"
export NODE_ENV=production

# Clean previous build
print_status "Cleaning previous build..."
rm -rf public/build
rm -rf node_modules/.vite

# Install dependencies
print_status "Installing dependencies..."
if npm install --silent; then
    print_success "Dependencies installed successfully"
else
    print_error "Failed to install dependencies"
    exit 1
fi

# Build assets
print_status "Building frontend assets..."
if npm run build --silent; then
    print_success "Frontend assets built successfully"
else
    print_error "Failed to build frontend assets"
    exit 1
fi

# Set proper permissions
print_status "Setting permissions..."
chmod -R 755 storage bootstrap/cache 2>/dev/null

print_success "🎉 Build completed successfully!"
print_status "System is ready for production!"



