<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StyleLab - Modern Fashion Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }
        .collection-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .collection-hover:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="index.php" class="text-2xl font-bold text-gray-800">StyleLab</a>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="men.php" class="text-gray-600 hover:text-gray-900 transition">Men</a>
                    <a href="women.php" class="text-gray-600 hover:text-gray-900 transition">Women</a>
                    <a href="kids.php" class="text-gray-600 hover:text-gray-900 transition">Kids</a>
                    <a href="accessories.php" class="text-gray-600 hover:text-gray-900 transition">Accessories</a>
                </div>
                <div class="flex items-center space-x-4">
                    <form action="search.php" method="GET" class="flex items-center">
                        <input type="text" name="query" placeholder="Search" class="border rounded-md px-3 py-2 w-48">
                        <button type="submit" class="ml-2">🔍</button>
                    </form>
                    <a href="login.php" class="text-gray-600 hover:text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                    <a href="cart.php" class="text-gray-600 hover:text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>
