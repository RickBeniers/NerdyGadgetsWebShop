<?php
include __DIR__ . "/header.php";
?>

<div style="text-align: center"><br>
	<h1>Gegevens invoeren</h1>
</div>

	<div class="container" style="width: 50%">
		<form>
			<br>
			<p>Aanhef</p>
			<div class="form-group">
				<div class="form-check form-check-inline">
					<input class="form-check-input" type="radio" name="aanhef" id="meneer" value="option1">
					<label class="form-check-label" for="inlineRadio1">Meneer</label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" type="radio" name="aanhef" id="mevrouw" value="option2">
					<label class="form-check-label" for="inlineRadio2">Mevrouw</label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" type="radio" name="aanhef" id="geenvanbeide" value="option3">
					<label class="form-check-label" for="inlineRadio3">Geen van beide</label>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<label for="voornaam">Voornaam</label>
					<input type="text" name="voornaam" class="form-control" placeholder="Voornaam">
				</div>
				<div class="col">
					<label for="tussenvoegsel">Tussenvoegsel</label>
					<input type="text" name="tussenvoegsel" class="form-control" placeholder="Tussenvoegsel">
				</div>
				<div class="col">
					<label for="achternaam">Achternaam</label>
					<input type="text" name="achternaam" class="form-control" placeholder="Achternaam">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col">
					<label for="postcode">Postcode</label>
					<input type="text" name="postcode" class="form-control" placeholder="Postcode">
				</div>
				<div class="col">
					<label for="huisnummer">Huisnummer</label>
					<input type="text" name="huisnummer" class="form-control" placeholder="Huisnummer">
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col">
					<label for="plaats">Plaats</label>
					<input type="text" name="plaats" class="form-control" placeholder="Plaats">
				</div>
				<div class="col">
					<label for="straatnaam">Straatnaam</label>
					<input type="text" name="straatnaam" class="form-control" placeholder="Straatnaam">
				</div>
			</div><br><br>
			<div class="row">
				<div class="col">
					<input type="submit" name="bestelknop" value="Bestellen en betalen" style="padding: 12px 32px;background: #686ef7;border-radius: 8px;color: white">
				</div>
			</div>
		</form>
	</div>


<?php
include __DIR__ . "/footer.php";
?>