from flask import Flask, request, jsonify, abort
from datetime import datetime, timedelta


import sqlite3

app = Flask(__name__, static_folder='static')

### DATABASE connector and creator method
def create_database():
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()

    # Execute the SQL script to create tables
    with open('schema.sql', 'r') as f:
        c.executescript(f.read())

    conn.commit()
    conn.close()


# Roles management
#
#
#Add a role
@app.route('/api/roles', methods=['GET'])
def get_roles():
    # Connect to database and get data
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT id, role_name FROM roles')
    roles = c.fetchall()
    conn.close()

    # Correct the structure
    roles_list = [{'id': role[0], 'role_name': role[1]} for role in roles]

    return jsonify(roles_list)


# Add a new role endpoint
@app.route('/api/roles', methods=['POST'])
def add_role():
    # get role from json
    new_role = request.json.get('role_name')
    # is json role is invalid return error
    if not new_role:
        return jsonify({"error": "Role name is required"}), 400

    #Connect to database
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()

    # Try catch block to insert role to db
    try:
        c.execute('INSERT INTO roles (role_name) VALUES (?)', (new_role,))
        conn.commit()
        conn.close()
        return jsonify({"message": f"Role '{new_role}' has been added."}), 201
    except sqlite3.IntegrityError:
        conn.close()
        return jsonify({"error": "Role already exists"}), 409


# Delete all roles endpoint
@app.route('/api/roles', methods=['DELETE'])
def delete_all_roles():
    # Connect to database
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('DELETE  FROM roles')
    conn.commit()
    conn.close()
    return jsonify({"message": "All roles have been deleted."}), 200


# User management
#
#
#Get all users
@app.route('/api/users', methods=['GET'])
def get_users():
    # Connect to database and get data
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM users')
    users = c.fetchall()
    conn.close()
    users_list = [{'id': user[0], 'name': user[1], 'surname':user[2],'country': user[3], 'address':user[4], 'email':user[5], 'username':user[6],'password':user[7], 'role_id':user[8],'approved':user[9]} for user in users]
    return jsonify(users_list)

# Get a user by ID
@app.route('/api/users/<int:user_id>', methods=['GET'])
def get_user_by_id(user_id):
    # Connect to database and get data
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    # Get user by id db query
    c.execute('SELECT * FROM users WHERE id = ?', (user_id,))
    user = c.fetchone()
    conn.close()
    # if the user is present in db then return the user, else return error message
    if user:
        user_data = {
            'id': user[0],
            'name': user[1],
            'surname': user[2],
            'country': user[3],
            'address': user[4],
            'email': user[5],
            'username': user[6],
            'role_id': user[8],
            'approved': user[9]
        }
        return jsonify(user_data)
    else:
        return jsonify({'error': 'User not found'}), 404
#Add a user
@app.route('/api/users', methods=['POST'])
def add_user():
    # Check if request is valid
    if not request.json or 'username' not in request.json or 'password' not in request.json:
        abort(400)

    # Initialize fields
    name = request.json['name']
    surname = request.json['surname']
    country = request.json['country']
    address = request.json['address']
    email = request.json['email']
    username = request.json['username']
    password = request.json['password']
    role_id = request.json.get('role_id', 2)  # Default to admin user role if not specified
    approved = request.json.get('approved')

    # Connect to db
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute(
        'INSERT INTO users (name,surname,country, address,email, username, password, role_id,approved) VALUES (?,?, ?, ?,?,?, ?, ?,?)',
        (name, surname, country, address, email, username, password, role_id,approved))
    conn.commit()
    # Retrieve the ID of the newly inserted user
    user_id = c.lastrowid
    conn.close()
    return jsonify(
        {'id': user_id, 'name': name, 'surname': surname, 'country': country, 'address': address, 'email': email,
         'username': username, 'role_id': role_id, 'approved': approved}), 201

#Update a user
@app.route('/api/users/<int:user_id>', methods=['PUT'])
def update_user(user_id):
    # Check if request is valid
    if not request.json:
        abort(400, description="Request must be JSON")

    # Get data from request
    username = request.json.get('username')
    password = request.json.get('password')
    role_id = request.json.get('role_id')
    approved = request.json.get('approved')
    name = request.json.get('name')
    surname = request.json.get('surname')
    email = request.json.get('email')
    country = request.json.get('country')
    address = request.json.get('address')

    # Convert 'approved' to integer for SQLite BOOLEAN
    # SQLite stores BOOLEAN as INTEGER 0 or 1
    if approved is not None:
        approved_value = 1 if approved in ['true', 'True', '1'] else 0
    else:
        approved_value = None

    # Connect to the database
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()

    # Prepare update statements
    update_fields = []
    params = []

    if username is not None:
        update_fields.append('username = ?')
        params.append(username)
    if password is not None:
        update_fields.append('password = ?')
        params.append(password)
    if role_id is not None:
        update_fields.append('role_id = ?')
        params.append(role_id)
    if approved_value is not None:
        update_fields.append('approved = ?')
        params.append(approved_value)
    if name is not None:
        update_fields.append('name = ?')
        params.append(name)
    if surname is not None:
        update_fields.append('surname = ?')
        params.append(surname)
    if email is not None:
        update_fields.append('email = ?')
        params.append(email)
    if country is not None:
        update_fields.append('country = ?')
        params.append(country)
    if address is not None:
        update_fields.append('address = ?')
        params.append(address)

    # Check if there is anything to update
    if not update_fields:
        abort(400, description="No fields provided to update")

    # Construct SQL query
    update_sql = 'UPDATE users SET ' + ', '.join(update_fields) + ' WHERE id = ?'
    params.append(user_id)

    # Execute the update query
    c.execute(update_sql, params)
    conn.commit()
    conn.close()

    # Fetch the updated user data
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM users WHERE id = ?', (user_id,))
    updated_user = c.fetchone()
    conn.close()

    if updated_user is None:
        abort(404, description="User not found")

    # Return updated user data
    return jsonify({
        'id': updated_user[0],
        'name': updated_user[1],
        'surname': updated_user[2],
        'country': updated_user[3],
        'address': updated_user[4],
        'email': updated_user[5],
        'username': updated_user[6],
        'password': updated_user[7],
        'role_id': updated_user[8],
        'approved': bool(updated_user[9])
    })

#Delete a user by user id
@app.route('/api/users/<int:user_id>', methods=['DELETE'])
def delete_user(user_id):
    #Connect to db
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('DELETE FROM users WHERE id = ?', (user_id,))
    conn.commit()
    conn.close()
    return jsonify({'result': 'User deleted'})

'''
# Registration requests management
# This method Fetches all registration requests from the database and returns them.
@app.route('/api/requests', methods=['GET'])
def get_requests():
    # Connect to db
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM requests')
    requests = c.fetchall()
    conn.close()
    requests_list = [{'id': request[0], 'username': request[1], 'status': request[2]} for request in requests]
    return jsonify(requests_list)


@app.route('/api/requests/<int:request_id>', methods=['PUT'])
def update_request(request_id):
    if not request.json or 'status' not in request.json:
        abort(400)
    status = request.json['status']

    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('UPDATE requests SET status = ? WHERE id = ?', (status, request_id))
    conn.commit()
    conn.close()

    return jsonify({'id': request_id, 'status': status})

'''


### SERVICES
# Retrive all services
@app.route('/api/gym_services', methods=['GET'])
def get_gym_services():
    #Connect to db
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM gym_services')
    services = c.fetchall()
    conn.close()
    services_list = [{'id': service[0], 'name': service[1], 'description': service[2], 'class_type': service[3],
                      'max_capacity': service[4]} for service in services]
    return jsonify(services_list)

# Get service by id
@app.route('/api/gym_services/<int:service_id>', methods=['GET'])
def get_gym_service(service_id):
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM gym_services WHERE id = ?', (service_id,))
    service = c.fetchone()
    conn.close()

    if service:
        service_data = {'id': service[0], 'name': service[1], 'description': service[2], 'class_type': service[3],
                        'max_capacity': service[4]}
        return jsonify(service_data)
    else:
        return jsonify({'error': 'Service not found'}), 404

#Add service
@app.route('/api/gym_services', methods=['POST'])
def create_gym_service():
    # Check if request is valid
    if not request.json or not 'name' in request.json:
        abort(400)
    service = {
        'name': request.json['name'],
        'description': request.json.get('description', ""),
        'class_type': request.json.get('class_type', ""),
        'max_capacity': request.json.get('max_capacity', 0)
    }

    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('INSERT INTO gym_services (name, description,class_type, max_capacity) VALUES (?, ?,?, ?)',
              (service['name'], service['description'], service['class_type'], service['max_capacity']))
    conn.commit()
    # Retrieve the ID of the newly inserted user
    service_id = c.lastrowid
    conn.close()
    service['id'] = service_id
    return jsonify(service), 201


# Update a service
@app.route('/api/gym_services/<int:service_id>', methods=['PUT'])
def update_gym_service(service_id):
    if not request.json:
        abort(400, description="No data provided")

    # Get updated fields from the request body
    service_name = request.json.get('name')
    description = request.json.get('description')
    class_type = request.json.get('class_type')
    max_capacity = request.json.get('max_capacity')

    # Ensure the service_id exists before updating
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM gym_services WHERE id = ?', (service_id,))
    service = c.fetchone()

    if service is None:
        conn.close()
        abort(404, description="Service not found")

    # Update the service's editable fields
    try:
        c.execute('''
            UPDATE gym_services
            SET name = ?, description = ?, class_type = ?, max_capacity = ?
            WHERE id = ?
        ''', (service_name, description, class_type, max_capacity, service_id))
        conn.commit()

        if c.rowcount == 0:
            conn.close()
            abort(404, description="No service updated, check data")
    except sqlite3.OperationalError as e:
        print(e)
        conn.close()
        abort(500, description=f"Database error: {e}")

    conn.close()
    return jsonify({
        'id': service_id,
        'name': service_name,
        'description': description,
        'class_type': class_type,
        'max_capacity': max_capacity
    })

#Delete service
@app.route('/api/gym_services/<int:service_id>', methods=['DELETE'])
def delete_gym_service(service_id):
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('DELETE FROM gym_services WHERE id = ?', (service_id,))
    conn.commit()
    conn.close()
    return jsonify({'result': 'Service deleted'})




# Announcements management
#
#
#Get all announcements
@app.route('/api/announcements', methods=['GET'])
def get_announcements():
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM announcements')
    announcements = c.fetchall()
    conn.close()
    announcements_list = [
        {'id': announcement[0], 'title': announcement[1], 'content': announcement[2], 'created_at': announcement[3]} for
        announcement in announcements]
    return jsonify(announcements_list)

# Add new announcement
@app.route('/api/announcements', methods=['POST'])
def add_announcement():
    if not request.json or 'title' not in request.json or 'content' not in request.json:
        abort(400)
    title = request.json['title']
    content = request.json['content']

    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('INSERT INTO announcements (title, content) VALUES (?, ?)', (title, content))
    conn.commit()
    # Retrieve the ID of the newly inserted user
    announcement_id = c.lastrowid
    conn.close()

    return jsonify({'id': announcement_id, 'title': title, 'content': content}), 201

#Delete an announcement by id
@app.route('/api/announcements/<int:announcement_id>', methods=['DELETE'])
def delete_announcement(announcement_id):
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('DELETE FROM announcements WHERE id = ?', (announcement_id,))
    conn.commit()
    conn.close()
    return jsonify({'result': 'Announcement deleted'})

# Bookings
#
#
# Get all bookings
@app.route('/api/bookings', methods=['GET'])
def get_bookings():
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM bookings')
    bookings = c.fetchall()
    conn.close()

    bookings_list = [{'id': booking[0], 'user_id': booking[1], 'schedule_id': booking[2],
                      'booking_time': booking[3], 'status': booking[4]} for booking in bookings]
    return jsonify(bookings_list)


# Get a single booking by ID
@app.route('/api/bookings/<int:booking_id>', methods=['GET'])
def get_booking(booking_id):
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM bookings WHERE id = ?', (booking_id,))
    booking = c.fetchone()
    conn.close()

    if booking:
        booking_data = {'id': booking[0], 'user_id': booking[1], 'schedule_id': booking[2],
                        'booking_time': booking[3], 'status': booking[4]}
        return jsonify(booking_data)
    else:
        return jsonify({'error': 'Booking not found'}), 404

# Add a booking
@app.route('/api/bookings', methods=['POST'])
def create_booking():
    if not request.json or not 'username' in request.json or not 'schedule_id' in request.json or not 'status' in request.json or not 'booking_time' in request.json:
        abort(400)

    try:
        # Parse the booking_time from the request JSON
        booking_time = datetime.strptime(request.json['booking_time'], '%Y-%m-%d %H:%M:%S')
    except ValueError:
        return jsonify({'error': 'Invalid date format. Please use YYYY-MM-DD HH:MM:SS.'}), 400

    username = request.json['username']
    schedule_id = request.json['schedule_id']
    status = request.json['status']

    # Fetch user_id based on username
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT id FROM users WHERE username = ?', (username,))
    user = c.fetchone()

    if user is None:
        conn.close()
        return jsonify({'error': 'User not found'}), 404

    user_id = user[0]

    # Calculate the date 7 days ago from now
    one_week_ago = datetime.now() - timedelta(days=7)

    # Check the number of cancellations in the past week for this user
    c.execute('SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = "cancelled" AND booking_time >= ?',
              (user_id, one_week_ago))
    cancellation_count = c.fetchone()[0]

    if cancellation_count > 2:
        conn.close()
        return jsonify({'error': 'You have exceeded the number of cancellations allowed in the past 7 days. Cannot make a new booking.'}), 403

    # Create the booking entry
    booking = {
        'user_id': user_id,
        'schedule_id': schedule_id,
        'booking_time': booking_time,
        'status': status
    }

    c.execute('INSERT INTO bookings (user_id, schedule_id, booking_time, status) VALUES (?, ?, ?, ?)',
              (booking['user_id'], booking['schedule_id'], booking['booking_time'], booking['status']))
    conn.commit()
    booking_id = c.lastrowid
    conn.close()

    booking['id'] = booking_id
    return jsonify(booking), 201


# Update an existing booking
@app.route('/api/bookings/<int:booking_id>', methods=['PUT'])
def update_booking(booking_id):
    if not request.json:
        abort(400)

    conn = sqlite3.connect('database1.db')
    c = conn.cursor()

    # Fetch the current booking data
    c.execute('SELECT * FROM bookings WHERE id = ?', (booking_id,))
    booking = c.fetchone()

    if not booking:
        return jsonify({'error': 'Booking not found'}), 404

    # Update the booking with new data
    user_id = request.json.get('user_id', booking[1])
    schedule_id = request.json.get('schedule_id', booking[2])
    status = request.json.get('status', booking[4])

    c.execute('UPDATE bookings SET user_id = ?, schedule_id = ?, status = ? WHERE id = ?',
              (user_id, schedule_id, status, booking_id))
    conn.commit()
    conn.close()

    updated_booking = {
        'id': booking_id,
        'user_id': user_id,
        'schedule_id': schedule_id,
        'status': status
    }

    return jsonify(updated_booking)

# Helper function to get user_id from username
def get_user_id_by_username(username):
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT id FROM users WHERE username = ?', (username,))
    user = c.fetchone()
    conn.close()
    return user[0] if user else None

# Fetch bookings by username
@app.route('/api/bookings/user/<username>', methods=['GET'])
def get_bookings_by_username(username):
    user_id = get_user_id_by_username(username)
    if not user_id:
        return jsonify({'error': 'User not found'}), 404

    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM bookings WHERE user_id = ?', (user_id,))
    bookings = c.fetchall()
    conn.close()

    # Prepare the bookings list
    bookings_list = []
    for booking in bookings:
        booking_data = {
            'id': booking[0],
            'user_id': booking[1],
            'schedule_id': booking[2],
            'booking_time': booking[3],
            'status': booking[4]
        }
        bookings_list.append(booking_data)

    return jsonify(bookings_list)

# Get booking by booking id
@app.route('/api/bookings/<int:booking_id>', methods=['DELETE'])
def delete_booking(booking_id):
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()

    # Check if the booking exists
    c.execute('SELECT * FROM bookings WHERE id = ?', (booking_id,))
    booking = c.fetchone()

    if not booking:
        conn.close()
        return jsonify({'error': 'Booking not found'}), 404

    # Update the booking status to "cancelled"
    c.execute('UPDATE bookings SET status = "cancelled" WHERE id = ?', (booking_id,))
    conn.commit()
    conn.close()

    return jsonify({'result': 'Booking cancelled successfully'})

# Get all programs
@app.route('/api/programs', methods=['GET'])
def get_programs():
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    #This SQL query retrieves program details and their associated gym service information by joining the `program` and `gym_services` tables on `service_id`.
    c.execute('''
        SELECT program.id, program.timeslot, gym_services.name, gym_services.description, gym_services.class_type
        FROM program
        JOIN gym_services ON program.service_id = gym_services.id
    ''')
    programs = c.fetchall()
    conn.close()

    programs_list = [{
        'id': program[0],
        'timeslot': program[1],
        'service': {
            'name': program[2],
            'description': program[3],
            'class_type': program[4]
        }
    } for program in programs]

    return jsonify(programs_list)


# Add a new program
@app.route('/api/programs', methods=['POST'])
def create_program():
    # Check request is valid
    if not request.json or not 'service_id' in request.json or not 'timeslot' in request.json:
        abort(400)

    try:
        # Parse the timeslot from the request JSON
        timeslot = datetime.strptime(request.json['timeslot'], '%Y-%m-%d %H:%M:%S')
    except ValueError:
        return jsonify({'error': 'Invalid date format. Please use YYYY-MM-DD HH:MM:SS.'}), 400

    program = {
        'service_id': request.json['service_id'],
        'timeslot': timeslot,
    }

    conn = sqlite3.connect('database1.db')
    c = conn.cursor()

    # Check if the service_id exists in the gym_services table
    c.execute('SELECT id FROM gym_services WHERE id = ?', (program['service_id'],))
    service = c.fetchone()

    if not service:
        conn.close()
        return jsonify({'error': 'Service ID does not exist'}), 400

    c.execute('INSERT INTO program (service_id, timeslot) VALUES (?, ?)',
              (program['service_id'], program['timeslot']))
    conn.commit()
    # Retrieve the ID of the newly inserted user
    program_id = c.lastrowid
    conn.close()

    program['id'] = program_id
    return jsonify(program), 201

#Delete program by program id
@app.route('/api/programs/<int:program_id>', methods=['DELETE'])
def delete_program(program_id):
    conn = sqlite3.connect('database1.db')
    c = conn.cursor()
    c.execute('SELECT * FROM program WHERE id = ?', (program_id,))
    program = c.fetchone()

    if not program:
        conn.close()
        return jsonify({'error': 'Program not found'}), 404

    c.execute('DELETE FROM program WHERE id = ?', (program_id,))
    conn.commit()
    conn.close()

    return jsonify({'message': 'Program deleted successfully'}), 200

# We start hereeee
if __name__ == '__main__':
    create_database()
    app.run(debug=True)
