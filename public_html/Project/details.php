<?php
require(__DIR__ . "/../../partials/nav.php");
//get the table definition
$result = [];
$result3 = [];
$columns = get_columns("Products");
//echo "<pre>" . var_export($columns, true) . "</pre>";
$ignore = ["id", "visibility", "modified", "created", "avg_rating" ,"num_rating"];
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
        $avg_rating = $r["avg_rating"];
        $avg_rating = round($avg_rating, 1);
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


$base_query = "SELECT Ratings.rating, Ratings.comment, Ratings.user_id, Users.username , Users.visibility FROM Ratings INNER JOIN Users on Users.id = Ratings.user_id";
$total_query = "SELECT count(1) as total FROM Ratings INNER JOIN Users on Users.id = Ratings.user_id";
//dynamic query
$query = " WHERE product_id = :product_id"; 
$params = [];
$params[":product_id"]=$id;
$query .= " ORDER BY Ratings.created DESC";
$per_page = 10;
paginate($total_query . $query, $params, $per_page);
$query .= " LIMIT :offset, :count";
$params[":offset"] = $offset;
$params[":count"] = $per_page;
//get the records
$stmt = $db->prepare($base_query . $query); //dynamically generated query
//we'll want to convert this to use bindValue so ensure they're integers so lets map our array
foreach ($params as $key => $value) {
    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $value, $type);
}
$params = null; //set it to null to avoid issues


try {
    $stmt->execute($params); //dynamically populated params to bind
    $s = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($s) {
        $result3 = $s;
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

        <div class="mb-4">
            <?php if($avg_rating!=0): ?>
                    <label class="form-label" for="avg_rating"> Average Rating</label>
                    <label class="form-control" for ="<?php se($avg_rating); ?>"> <?php se($avg_rating); ?>  / 5</label>
            <?php endif; ?>    
                </div>
        <?php include(__DIR__. "/../../partials/pagination.php"); ?>
                
        <?php foreach ($result3 as $item) : ?>
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Rating: <?php se($item,"rating"); ?>/5 by <?php 
                            $visibility = se($item, "visibility", 0, false);
                            if($visibility){
                                $user_id = se($item, "user_id", 0, false);
                                $username = se($item, "username", "", false);
                                include(__DIR__ . "/user_profile_link.php");}
                            else{?>
                                Anonymous
                            <?php } ?>

                        </h5> 
                    </div>
                    <div class="card-body">
                         <?php se($item, "comment"); ?>
                       
                    </div>
                </div>
            </div> 
        <?php endforeach; ?>
</div>
