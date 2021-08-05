<?php include "templates/include/header.php" ?>

	<h1 class="singleArticle"><?php echo htmlspecialchars($results['article']->title) ?></h1>
	<div class="singleArticleContent singleSummary"><?php echo htmlspecialchars($results['article']->summary) ?></div>
	<div class="singleArticleContent"><?php echo $results['article']->content ?></div>
	<p class="pubDate">Published on <?php echo date('j F Y', $results['article']->publicationDate) ?></p>

	<p><a href="./">Return to homepage</a></p>

<?php include "templates/include/footer.php" ?>