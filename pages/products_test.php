<?php
include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";
?>

<div class="container mt-5 pt-5">

<h2>Product Testing Page</h2>


<!-- PRODUCTS -->
<div class="products" id="productList">

    <div class="product-card" data-name="Coca Cola" data-price="2.50">
        <h3>Coca Cola</h3>
        <p>$2.50</p>
        <button class="add-cart">Add to Cart</button>
    </div>

    <div class="product-card" data-name="Pepsi" data-price="2.20">
        <h3>Pepsi</h3>
        <p>$2.20</p>
        <button class="add-cart">Add to Cart</button>
    </div>

    <div class="product-card" data-name="Sprite" data-price="2.30">
        <h3>Sprite</h3>
        <p>$2.30</p>
        <button class="add-cart">Add to Cart</button>
    </div>

    <div class="product-card" data-name="Red Bull" data-price="3.50">
        <h3>Red Bull</h3>
        <p>$3.50</p>
        <button class="add-cart">Add to Cart</button>
    </div>

</div>

<hr>

<!-- CART -->
<h2>Cart</h2>

<div id="cart">

</div>

</main>

<script src="/js/main.js"></script>

<?php include __DIR__ . "/../components/footer.php"; ?>