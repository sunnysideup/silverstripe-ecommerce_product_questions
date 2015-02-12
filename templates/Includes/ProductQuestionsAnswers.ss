<% if ProductQuestionsAnswers %>
	<ul class="productQuestionsAnswers">
		<% loop ProductQuestionsAnswers %>
		<li>
			<span class="productQuestion">
				<strong class="productQuestionsLabel">$Label</strong>:
				<em class="productQuestionsAnswer">$Answer</em>
			</span>
		</li>
		<% end_loop %>
	</ul>
<% end_if %>
