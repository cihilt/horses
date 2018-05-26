<?php

if (isset($_POST['date']) && !empty($_POST['date'])) {

    $str_date = strtotime($_POST['date']);

    
    include('results_core.php');
    
    
}

?>

<!-- Select date form -->
<!-- jquery -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
$( function() {
  $( "#date" ).datepicker({maxDate: '0'}).datepicker("setDate", new Date());
} );
</script>
<!-- .jquery -->
<!-- bootstrap -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
<!-- .bootstrap -->
<div class="container">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">
      <h1>Scrapping for `results` table</h1>
      <form method="post">
        <div class="form-group">
          <label for="date">Select date</label>
          <input type="text" name="date" class="form-control" id="date" aria-describedby="dateHelp" placeholder="Select date" required>
          <small id="dateHelp" class="form-text text-muted">Keep in mind that need to run index2.php script first <a href="/index2.php">index2.php</a> for races table filling.</small>
        </div>
        <button type="submit" class="btn btn-primary">Scrape</button>
      </form>
      <?php if (isset($msg['success']) && !empty($msg['success'])) : ?>
      <?php foreach($msg['success'] as $message) : ?>
      <div class="alert alert-success" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>        
        <?php echo $message; ?>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if (isset($msg['info']) && !empty($msg['info'])) : ?>
      <?php foreach($msg['info'] as $message) : ?>
      <div class="alert alert-info" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>        
        <?php echo $message; ?>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if (isset($msg['danger']) && !empty($msg['danger'])) : ?>
      <?php foreach($msg['danger'] as $message) : ?>
      <div class="alert alert-danger" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <?php echo $message; ?>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<!-- .Select date form -->
