#!/bin/bash

# 🏗️ Build Assets for Shared Hosting Deployment
# Run this on your LOCAL machine before uploading to server

echo "================================"
echo "🏗️  Building for Deployment"
echo "================================"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

PROJECT_DIR="/Users/mo/Documents/project/AdvancedCouponSystem"

cd "$PROJECT_DIR" || exit

echo -e "${GREEN}Step 1: Installing NPM Dependencies${NC}"
npm install

echo -e "${GREEN}Step 2: Building Production Assets${NC}"
npm run build

echo -e "${GREEN}Step 3: Installing Composer Dependencies (Production)${NC}"
composer install --optimize-autoloader --no-dev

echo -e "${GREEN}Step 4: Optimizing Composer Autoloader${NC}"
composer dump-autoload --optimize --classmap-authoritative

echo -e "${GREEN}Step 5: Verifying Build Files${NC}"
if [ -d "public/build" ]; then
    echo -e "${GREEN}✅ Build directory exists${NC}"
    echo "Files in public/build:"
    ls -lh public/build/ | head -10
else
    echo -e "${YELLOW}⚠️  Build directory not found!${NC}"
    exit 1
fi

if [ -f "public/build/manifest.json" ]; then
    echo -e "${GREEN}✅ Manifest file exists${NC}"
else
    echo -e "${YELLOW}⚠️  Manifest file not found!${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}Step 6: Creating Deployment Package${NC}"
DATE=$(date +%Y%m%d_%H%M%S)
PACKAGE_NAME="deploy_${DATE}.tar.gz"

# Create tar excluding unnecessary files
tar -czf "../$PACKAGE_NAME" \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='tests' \
  --exclude='.env' \
  --exclude='storage/logs/*.log' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  .

echo -e "${GREEN}✅ Package created: ../$PACKAGE_NAME${NC}"
echo ""

# Get package size
PACKAGE_SIZE=$(du -h "../$PACKAGE_NAME" | cut -f1)
echo -e "📦 Package size: ${YELLOW}$PACKAGE_SIZE${NC}"

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}✅ Build Complete!${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Upload ../$PACKAGE_NAME to your server"
echo "2. Extract: tar -xzf $PACKAGE_NAME"
echo "3. Follow SHARED_HOSTING_GUIDE.md for setup"
echo ""
echo -e "${YELLOW}Important Files to Upload:${NC}"
echo "  ✅ public/build/ (entire directory)"
echo "  ✅ vendor/ (Composer dependencies)"
echo "  ✅ All PHP files (app/, routes/, config/, etc.)"
echo ""
echo -e "${YELLOW}Files NOT to upload:${NC}"
echo "  ❌ node_modules/"
echo "  ❌ .git/"
echo "  ❌ tests/"
echo "  ❌ .env (create new on server)"
echo ""

