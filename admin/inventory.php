<?php
include "../config/db.php";

if(isset($_POST['add'])){

$name=$_POST['name'];
$price=$_POST['price'];
$stock=$_POST['stock'];
$image=$_FILES['image']['name'];

move_uploaded_file($_FILES['image']['tmp_name'],"../assets/images/".$image);

$conn->query("INSERT INTO menu_items(name,price,stock,image)
VALUES('$name','$price','$stock','$image')");

}

?>

<h2>Add Menu Item</h2>

<form method="POST" enctype="multipart/form-data">

Name
<input type="text" name="name">

Price
<input type="text" name="price">

Stock
<input type="number" name="stock">

Image
<input type="file" name="image">

<button name="add">Add Item</button>

</form>