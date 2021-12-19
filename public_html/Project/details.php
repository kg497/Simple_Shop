<?php
require(__DIR__ . "/../../partials/nav.php");
//get the table definition
$result = [];
$columns = get_columns("Products");
//echo "<pre>" . var_export($columns, true) . "</pre>";
$ignore = ["id", "visibility", "modified", "created"];
$db = getDB();
//get the item
$id = se($_GET, "id", -1, false);
$user_id = get_user_id();
$stmt = $db->prepare("SELECT * FROM Products where id =:id");
try {
    $stmt->execute([":id" => $id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $result = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}
$purchase = false;
$stmt2 = $db->prepare("SELECT Orders.id FROM Orders INNER JOIN OrderItems ON OrderItems.order_id = Orders.id WHERE Orders.user_id = :user_id && OrderItems.product_id = :product_id");
try {
    $stmt2->execute([":user_id" => $user_id, ":product_id" => $id ]);
    $m = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($m) {
        $purchase = true;
    }
    
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}
function mapColumn($col)
{
    global $columns;
    foreach ($columns as $c) {
        if ($c["Field"] === $col) {
            return inputMap($c["Type"]);
        }
    }
    return "text";
}

?>
<div class="container-fluid">
    <h1>Item Detail Page</h1>
        <?php foreach ($result as $column => $value) : ?>
            <?php /* Lazily ignoring fields via hardcoded array*/ ?>
            <?php if (!in_array($column, $ignore)) : ?>
                <div class="mb-4">
                    <label class="form-label" for="<?php se($column); ?>"><?php se($column); ?></label>
                    <label class="form-control" for ="<?php se($value); ?>"> <?php se($value); ?>  </label>
                    
                </div>
                
            <?php endif; ?>
            
        <?php endforeach; ?>
        <?php if($purchase) :?>
        <div class="mb-4">
            <input type=button onClick="location.href='ratings.php?id=<?php se($id)?>'" class = "btn btn-primary" value='Rate'>
        </div>
        <?php endif;?>
        <?php if(has_role("Admin")) : ?>
            <a href="admin/edit_item.php?id=<?php se($id); ?>">Edit</a>
        <?php endif; ?>
</div>
