<?php
include "../config/db.php";

$query = "
SELECT menu_items.name, SUM(order_items.quantity) as total
FROM order_items
JOIN menu_items ON order_items.item_id = menu_items.id
GROUP BY item_id
ORDER BY total DESC
";

$res = $conn->query($query);

echo "<h2>Popular Items</h2>";

while($row=$res->fetch_assoc()){

echo $row['name']." : ".$row['total']." orders<br>";

}
?>