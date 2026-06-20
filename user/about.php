<?php
// user/about.php
require_once '../includes/session.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h2 class="section-title"><i class="fa-solid fa-mug-hot me-2 text-gold"></i>About CFOMS</h2>
            <p class="lead text-light">Revolutionizing the way you experience university dining.</p>
            <p class="text-muted">
                CFOMS (Canteen Food Order Management System) is a state-of-the-art multi-vendor marketplace designed specifically for university campuses. 
                We bridge the gap between hungry students and the diverse culinary options available on campus.
            </p>
            <p class="text-muted">
                Gone are the days of standing in long lines or wondering what's on the menu. With CFOMS, you can browse, order, and track your meals from the comfort of your classroom or dorm.
            </p>
        </div>
        <div class="col-md-6 text-center">
            <div class="premium-card p-4 d-inline-block">
                <i class="fa-solid fa-utensils fa-5x text-gold mb-3"></i>
                <h4 class="text-light">10+ Canteens</h4>
                <p class="text-muted mb-0">Under one digital roof</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="premium-card p-4 text-center h-100">
                <i class="fa-solid fa-bolt fa-3x text-gold mb-3"></i>
                <h5 class="text-light">Fast & Efficient</h5>
                <p class="text-muted">Skip the queue with our pre-order system.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="premium-card p-4 text-center h-100">
                <i class="fa-solid fa-shield-halved fa-3x text-gold mb-3"></i>
                <h5 class="text-light">Secure & Reliable</h5>
                <p class="text-muted">Your data and orders are always safe with us.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="premium-card p-4 text-center h-100">
                <i class="fa-solid fa-users fa-3x text-gold mb-3"></i>
                <h5 class="text-light">Community Driven</h5>
                <p class="text-muted">Rated and reviewed by students like you.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
