<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Panel - CFOMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        :root {
            --admin-primary: #7c3aed;
            --admin-dark: #0f0c29;
            --admin-card: #1e1b4b;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #1e1b4b 100%);
            color: #f5f5f5;
            min-height: 100vh;
        }
        .navbar-admin {
            background: rgba(15, 12, 41, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(124, 58, 237, 0.2);
        }
        .navbar-admin .navbar-brand { color: #7c3aed !important; font-weight: 700; }
        .navbar-admin .nav-link { color: #f5f5f5 !important; }
        .navbar-admin .nav-link:hover { color: #7c3aed !important; }
        .admin-card {
            background: rgba(30, 27, 75, 0.8);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 16px;
        }
        .admin-card:hover {
            border-color: rgba(124, 58, 237, 0.5);
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.1);
        }
        .btn-admin {
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            border: none;
            color: #fff;
        }
        .btn-admin:hover { color: #fff; transform: translateY(-2px); }
        .btn-admin-outline {
            background: transparent;
            border: 2px solid #7c3aed;
            color: #7c3aed;
        }
        .btn-admin-outline:hover { background: #7c3aed; color: #fff; }
        .text-admin { color: #7c3aed !important; }
        .table-admin { --bs-table-bg: transparent; --bs-table-color: #f5f5f5; }
        .footer-admin {
            background: rgba(15, 12, 41, 0.95);
            border-top: 1px solid rgba(124, 58, 237, 0.2);
            padding: 1rem 0;
        }
    </style>
</head>
<body>
