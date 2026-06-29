-- Reset password for User ID 1 to Mas@4001
-- Hash generated with PHP password_hash('Mas@4001', PASSWORD_DEFAULT) / bcrypt

UPDATE `users`
SET `password` = '$2b$12$/8TxdWD3.7KnSPC5C5dpSOWKiLs35CbQSF4YhS0oB.hTcDxOd3zQW'
WHERE `id` = 1;
