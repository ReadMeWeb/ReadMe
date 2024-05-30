import json
import random

with open("book_data.json") as f:
    file_content_str = f.read()
    file_content_json = json.loads(file_content_str)

    insert_authors_query = ""
    insert_books_query = ""

    author_id = 1
    for author_data in file_content_json:
        author_name = author_data["author"]
        insert_authors_query += f"INSERT INTO Authors (id, name_surname) VALUES ({author_id}, '{author_name}');\n"
        author_books = author_data["books"]
        for book_data in author_books:
            title: str = book_data["title"]
            description: str = book_data["description"]
            n_of_copies = random.randint(8, 15)
            title = title.replace("'", "\\'")
            path = title.replace("\\'", "").replace(" ", "_") + ".jpg"
            description = description.replace("'", "\\'")
            # print(title.replace(" ", "_"))
            insert_books_query += f"INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('{title}', '{description}', '{path}', {n_of_copies}, {author_id});\n"
        author_id += 1

    final_query = insert_authors_query + insert_books_query
    print(final_query)
