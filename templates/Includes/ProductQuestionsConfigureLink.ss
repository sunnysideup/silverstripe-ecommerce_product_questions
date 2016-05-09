<div class="productQuestionsAnswerHolder">
    <% include ProductQuestionsAnswers %>
    <% if CanConfigure %>
    <div class="configureLinkHolder <% if HasRequiredQuestions %>required<% end_if %>">
        <% if HasRequiredQuestions %>
            $ProductQuestionsAnswerFormInCheckoutPage
        <% else %>
            <a href="$ConfigureLink" class="configureLink">$ConfigureLabel</a>
        <% end_if %>
    </div>
    <% end_if %>
</div>
