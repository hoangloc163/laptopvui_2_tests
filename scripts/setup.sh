#!/usr/bin/env bash
# scripts/setup.sh - First-time setup for laptop-vui-tests
set -e

echo "🔧 Laptop Vui Tests - Setup"
echo "=========================="

# Check prerequisites
command -v php >/dev/null 2>&1 || { echo "❌ PHP not found. Install PHP 8.4+"; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "❌ Composer not found. Install Composer 2+"; exit 1; }
command -v node >/dev/null 2>&1 || { echo "❌ Node.js not found. Install Node 20+"; exit 1; }
command -v npm >/dev/null 2>&1 || { echo "❌ npm not found."; exit 1; }

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
NODE_VERSION=$(node -v)
echo "✅ PHP: $PHP_VERSION"
echo "✅ Node: $NODE_VERSION"

# Install PHP dependencies
echo ""
echo "📦 Installing PHP dependencies..."
composer install --prefer-dist --no-progress

# Install Node dependencies
echo ""
echo "📦 Installing Node dependencies..."
npm install

# Install Playwright browsers
echo ""
echo "🎭 Installing Playwright browsers..."
npx playwright install --with-deps chromium firefox webkit

# Create .env from example
if [ ! -f .env ]; then
  echo ""
  echo "📄 Creating .env from .env.example..."
  cp .env.example .env
  echo "   Edit .env if you need to change APP_URL"
fi

echo ""
echo "✅ Setup complete!"
echo ""
echo "Next steps:"
echo "  1. Start app: cd ../banhang && php -S localhost:8000 -t . dev-router.php"
echo "  2. Run PHPUnit: composer test"
echo "  3. Run E2E: npm run test:e2e"
echo ""
echo "See README.md for more details."
