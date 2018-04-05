# mvesign-web-api
API to retrieve information that is going to be displayed on https://www.mvesign.com/.

---

#### Retrieve articles
<span style="color:#990000">**`GET`**</span> `/articles/{reference}?take={take}&skip={skip}`

###### Request
```
GET /articles/php?take=1&skip=0 HTTP/1.1
```

###### Response
```
[
  {
    "reference": "12345678-1234-1234-1234-1234567890AB",
    "title": "Some title",
    "content": "Content of the article in Markdown format.",
    "createdOn": "2018-04-01 12:00:00",
    "tags": [
        "php"
    ]
  }
]
```

---

#### Retrieve articles by tag
<span style="color:#990000">**`GET`**</span> `/tags/{reference}?take={take}&skip={skip}`

###### Request
```
GET /tags/php?take=1&skip=0 HTTP/1.1
```

###### Response
```
[
  {
    "reference": "12345678-1234-1234-1234-1234567890AB",
    "title": "Some title",
    "content": "Content of the article in Markdown format.",
    "createdOn": "2018-04-01 12:00:00",
    "tags": [
        "php"
    ]
  }
]
```

---

#### Retrieve article by reference
<span style="color:#990000">**`GET`**</span> `/articles/{reference}`

###### Request
```
GET /articles/{reference} HTTP/1.1
```

###### Response
```
{
  "reference": "12345678-1234-1234-1234-1234567890AB",
  "title": "Some title",
  "content": "Content of the article in Markdown format.",
  "createdOn": "2018-04-01 12:00:00",
  "tags": [
      "php"
  ]
}
```

---

#### Create article
<span style="color:#990000">**`POST`**</span> `/articles`

###### Request
```
POST /articles HTTP/1.1
Content-Type: application/json
X-MVESIGN-PASSWORD: f97f85f578bc3c45bf16b58cef26c73c2dac66e0c7fb98f6fa7fda4564e9451e

{
  "title": "Some title",
  "content": "Content of the article in Markdown format.",
  "tags": [
      "php"
  ]
}
```

###### Response
```
{
  "reference": "12345678-1234-1234-1234-1234567890AB",
  "title": "Some title",
  "content": "Content of the article in Markdown format.",
  "createdOn": "2018-04-01 12:00:00",
  "tags": [
      "php"
  ]
}
```

---

#### Retrieve summary of articles
<span style="color:#990000">**`GET`**</span> `/summary/articles`

###### Request
```
GET /summary/articles HTTP/1.1
```

###### Response
```
{
  "numberOfItems": "30",
  "itemsPerPage": "10",
  "numberOfPages": "3",
  "currentPage": "1"
}
```

---

#### Retrieve summary of articles by tag
<span style="color:#990000">**`GET`**</span> `/summary/tags/{reference}`

###### Request
```
GET /summary/tags/php HTTP/1.1
```

###### Response
```
{
  "numberOfItems": "5",
  "itemsPerPage": "10",
  "numberOfPages": "1",
  "currentPage": "1"
}
```