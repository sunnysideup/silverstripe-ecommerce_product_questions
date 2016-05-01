<div class="productQuestionsAnswerHolder">
	<% include ProductQuestionsAnswers %>
	<% if CanConfigure %>
    <div class="configureLinkHolder <% if HasRequiredQuestions %>required<% end_if %>">
		<a href="$ConfigureLink" class="configureLink">$ConfigureLabel</a>
	</div>
    <% end_if %>
</div>
