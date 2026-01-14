SELECT u.id, u.email, r.name as rol
FROM users u
LEFT JOIN model_has_roles mr ON u.id = mr.model_id
LEFT JOIN roles r ON mr.role_id = r.id
WHERE u.email = 'daniel25012025@gmail.com';
