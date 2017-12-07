<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>타이틀</title>
	<link href="<?php echo _NODE?>/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
	<link href="<?php echo _CSS?>/Footer-Clean.css" rel="stylesheet">
	<link href="<?php echo _CSS?>/common.css" rel="stylesheet">
	<link href="<?php echo _CSS?>/index.css" rel="stylesheet">
</head>

<body>
<?php
require_once "header.php";
?>
	<!-- 메인 -->
	<article class="index">
		<div id="board-header">
			<div class="title">
				<h2>전체 질문</h2>
			</div>
			<div class="btn-order">
				<a href="#">최신순</a>
				<a href="#">조회순</a>
			</div>
		</div>
		<div class="board">
			<ul class="list-group">
				<script type="text/handlebars-template" id="questions-template">
					{{#each this}}
					<li class="list-group-item" data-id={{question_id}}>
						<span class="view-cnt badge">{{view}}</span>
						<p>
							<a href="#" class="list-head">{{title}}</a>
							<br>
							<div class="list-footer">
								<span class="author">{{user_id}}</span>
								<span class="date">{{modify_date}}</span>
								<ul class="tags">
									{{#each tags}}
									<li class="tags">
										<span class="label label-info">{{this}}</span>
									</li>
									{{/each}}
								</ul>
							</div>
						</p>
					</li>
					{{/each}}
				</script>
			</ul>
			<nav id="pageNav" aria-label="Page navigation">
				<ul class="pagination">
				<script type="text/handlebars-template" id="pagination-template">
					<li class="previous">
						<a href="#" aria-label="Previous">
							<span aria-hidden="true">&laquo;</span>
						</a>
					</li>
					{{#each this}}
					<li class="pageNum" data-num={{this}}>
						<a href="#">{{this}}</a>
					</li>
					{{/each}}
					<li class="next">
						<a href="#" aria-label="Next">
							<span aria-hidden="true">&raquo;</span>
						</a>
					</li>
					</script>
				</ul>
			</nav>
		</div>
	</article>
<?php
require_once "footer.php";
?>
	<script src="<?php echo _NODE ?>/jquery/dist/jquery.js"></script>
	<script src="<?php echo _NODE ?>/bootstrap/dist/js/bootstrap.js"></script>
	<script src="<?php echo _NODE ?>/handlebars/dist/handlebars.js"></script>
	<script src="<?php echo _JS ?>/index.js"></script>
	<script src="<?php echo _JS ?>/common.js"></script>

</body>

</html>