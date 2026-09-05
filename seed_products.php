<?php
// seed_products.php
// Run this ONCE to create 300 products.

require "config.php";

$categories=[
"Electronics",
"Fashion",
"Shoes",
"Beauty",
"Home",
"Books",
"Sports",
"Accessories",
"Toys",
"Grocery"
];

$products=[
"Wireless Headphones",
"Smart Watch",
"Running Shoes",
"Backpack",
"Bluetooth Speaker",
"Gaming Mouse",
"Mechanical Keyboard",
"Sunglasses",
"Fitness Band",
"Travel Bottle"
];

$images=[
"https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800",
"https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800",
"https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800",
"https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800",
"https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800"
];

for($i=1;$i<=300;$i++) {

    $name =
        $products[($i-1)%count($products)]
        ." Model ".$i;

    $category =
        $categories[($i-1)%count($categories)];

    $brand="ConnectBrand";

    $description=
        "Premium ".$category.
        " product number ".$i.
        ". Designed for everyday use.";

    $price=
        499+(($i*173)%9000);

    $stock=
        10+($i%50);

    $image=
        $images[($i-1)%count($images)];

    $rating=
        4+(($i%10)/10);

    $stmt=$conn->prepare(
        "INSERT INTO products
        (name,category,brand,description,
         price,stock,image,rating)
        VALUES(?,?,?,?,?,?,?,?)"
    );

    $stmt->bind_param(
        "ssssdiss",
        $name,
        $category,
        $brand,
        $description,
        $price,
        $stock,
        $image,
        $rating
    );

    $stmt->execute();
}

echo "300 products created successfully.";

?>