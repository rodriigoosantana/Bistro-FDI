CREATE USER 'bistro_fdi'@'%' IDENTIFIED BY 'bistro_fdi';
GRANT ALL PRIVILEGES ON `bistro_fdi`.* TO 'bistro_fdi'@'%';

CREATE USER 'bistro_fdi'@'localhost' IDENTIFIED BY 'bistro_fdi';
GRANT ALL PRIVILEGES ON `bistro_fdi`.* TO 'bistro_fdi'@'localhost';