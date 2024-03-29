<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "home.php"));
}
$db = getDB();
$results = [];
$params =[];
$base_query = "SELECT id, name, description, category, stock, unit_price, visibility from Products";
$total_query = "SELECT count(1) as total FROM Products";
$query = " WHERE 1=1";
$name = se($_GET, "itemName", "", false);
if (!empty($name)) {
    $query .= " AND name like :name";
    $params[":name"] = "%$name%";
}
$stock = se($_GET, "stock", -1, false);
if(!empty($stock)){
    $query .= " AND stock <= :stock";
    $params[":stock"]= $stock;
}
$per_page = 5;
paginate($total_query . $query, $params, $per_page);
$query .= " LIMIT :offset, :count";
$params[":offset"] = $offset;
$params[":count"] = $per_page;
//get the records
$stmt = $db->prepare($base_query . $query); //dynamically generated query

foreach ($params as $key => $value) {
    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $value, $type);
}
$params = null; //set it to null to avoid issues

try {
    $stmt->execute($params); //dynamically populated params to bind
    $s = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($s) {
        $results = $s;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}

?>
<div class="container-fluid">
    <h1>List Items</h1>
    <form method="GET" class="row row-cols-lg-auto g-3 align-items-center">
        <div class="input-group mb-3">
            <input class="form-control" type="search" name="itemName" placeholder="Item Filter" />
            
        </div>
        <div class="input-group mb-3">
            <label class="form-label" for="pw">Search Items with Stock less than or equal to</label>
            <input  type="number" id="stock" name="stock" />
        </div>
        <div class = "mb-3">
            <input class="btn btn-primary" type="submit" value="Search" />
        </div>

    </form>
        
    <?php if (count($results) == 0) : ?>
        <p>No results to show</p>
    <?php else : ?>
    <?php include(__DIR__. "/../../../partials/pagination.php"); ?>
        <table class="table text-dark">
            <?php foreach ($results as $index => $record) : ?>
                <?php if ($index == 0) : ?>
                    <thead>
                        <?php foreach ($record as $column => $value) : ?>
                            <th><?php se($column); ?></th>
                        <?php endforeach; ?>
                        <th>Actions</th>
                    </thead>
                <?php endif; ?>
                <tr>
                    <?php foreach ($record as $column => $value) : ?>
                        <td><?php se($value, null, "N/A"); ?></td>
                    <?php endforeach; ?>


                    <td>
                        <a href="edit_item.php?id=<?php se($record, "id"); ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>