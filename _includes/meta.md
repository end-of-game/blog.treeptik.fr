{{page.date | date: "%B %e, %Y"}}

    {% for tag in page.tags %}
    {{ tag }}
    {% endfor %}
