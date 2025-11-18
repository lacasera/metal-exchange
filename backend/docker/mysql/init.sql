-- Create main application database
CREATE DATABASE IF NOT EXISTS metal_exchange
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Create testing database (for CI or phpunit)
CREATE DATABASE IF NOT EXISTS metal_exchange_testing
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Grant privileges
GRANT ALL PRIVILEGES ON metal_exchange.* TO 'metal'@'%';
GRANT ALL PRIVILEGES ON metal_exchange_testing.* TO 'metal'@'%';

FLUSH PRIVILEGES;