<?php

include($_SERVER['DOCUMENT_ROOT']."/includes/session.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/signalnoise.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/connect.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/functions.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/header.php");

?>
<div class="row">
<div class="column">

<?php
// get all card data:

$query = "select * from tblcards";
$result = mysqli_query($connection, $query) or die ("couldn't execute query");
if (mysqli_num_rows($result) > 0) {
echo '<ol id="cardList">';
$cardDataNeeded = array(array(null,null,null));
while ($row = mysqli_fetch_array($result)) {
extract($row);
?>

<li>
	<img src="/images/card-game/cards/<?php echo $cardID; ?>.png" alt="<?php echo $cardName; ?>">
	<h4><?php echo $cardName; ?></h4>
	 <dl>
  <dt>Attack</dt>
  <dd><?php echo $cardAttack; ?></dd>
  <dt>Defense</dt>
  <dd><?php echo $cardDefense; ?></dd>
    <dt>Cost to craft</dt>
  <dd><?php echo $cardCraftingCost; ?></dd>
</dl> 
</li>

<?php




	}
	echo "</ol>";
}




?>









</div>
</div>




<?php

include($_SERVER['DOCUMENT_ROOT']."/includes/navigation/card-game.php");
include($_SERVER['DOCUMENT_ROOT']."/includes/footer.php");
?>