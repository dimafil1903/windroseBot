<form method="POST" action="https://www.liqpay.ua/api/3/checkout" accept-charset="utf-8">
    <input type="hidden" name="data" value="{{$data}}"/>
    <input type="hidden" name="signature" value="{{$signature}}"/>
    <input type="image" src="//static.liqpay.ua/buttons/p1ru.radius.png"/>
</form>

<form method="POST" accept-charset="utf-8" action="https://www.liqpay.ua/api/3/checkout">
    <input type="hidden" name="data" value="eyJ2ZXJzaW9uIjozLCJhY3Rpb24iOiJwYXkiLCJwdWJsaWNfa2V5IjoiaTI2NDA1OTY3MzgiLCJhbW91bnQiOiI1IiwiY3VycmVuY3kiOiJVQUgiLCJkZXNjcmlwdGlvbiI6ItCc0L7QuSDRgtC+0LLQsNGAIiwidHlwZSI6ImJ1eSIsInNhbmRib3giOiIxIiwibGFuZ3VhZ2UiOiJydSJ9" />
    <input type="hidden" name="signature" value="npnUqnh92Q3/+j4N5KvW5WTYzo8=" />
    <input type="image" src="//static.liqpay.ua/buttons/p1ru.radius.png" name="btn_text" />
</form>
