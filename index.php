<!DOCTYPE html>
<html>
<head>
    <title>Seminar Registration with Camera</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Seminar Registration with Photo Capture</h1>
    
    <div class="container">
        <!-- Registration Form Section -->
        <div class="form-section">
            <h2>Registration Form</h2>
            <form id="registrationForm" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" required>
                    <span class="error-message" id="nameError"></span>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                    <span class="error-message" id="emailError"></span>
                </div>

                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="tel" id="phone" name="phone" required>
                    <span class="error-message" id="phoneError"></span>
                </div>

                <div class="form-group">
                    <label for="company">Company</label>
                    <input type="text" id="company" name="company">
                </div>

                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position">
                </div>

                <div class="form-group">
                    <label for="seminarTopic">Seminar Topic *</label>
                    <select id="seminarTopic" name="seminarTopic" required>
                        <option value="">Select a topic</option>
                        <option value="Web Development">Web Development</option>
                        <option value="Data Science">Data Science</option>
                        <option value="Machine Learning">Machine Learning</option>
                        <option value="Cyber Security">Cyber Security</option>
                        <option value="Cloud Computing">Cloud Computing</option>
                    </select>
                    <span class="error-message" id="topicError"></span>
                </div>

                <div class="form-group">
                    <label for="dietary">Dietary Requirements</label>
                    <select id="dietary" name="dietary">
                        <option value="None">None</option>
                        <option value="Vegetarian">Vegetarian</option>
                        <option value="Vegan">Vegan</option>
                        <option value="Gluten-Free">Gluten-Free</option>
                        <option value="Dairy-Free">Dairy-Free</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="comments">Comments</label>
                    <textarea id="comments" name="comments" rows="4"></textarea>
                </div>

                <!-- Hidden field for photo data -->
                <input type="hidden" id="photoData" name="photoData">

                <div class="form-actions">
                    <button type="button" id="resetBtn" class="btn btn-secondary">Reset Form</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Complete Registration</button>
                </div>
            </form>
        </div>

        <!-- Camera Section -->
        <div class="camera-section">
            <h2>Take Profile Photo</h2>
            
            <div id="cameraNotSupported" class="warning-message" style="display: none;">
                ⚠️ Camera may not work with file:// protocol. Please use http://localhost
            </div>
            
            <video id="videoElement" autoplay playsinline></video>
            <canvas id="canvas" style="display: none;"></canvas>
            
            <div class="camera-controls">
                <button id="startButton" class="btn btn-primary">Start Camera</button>
                <button id="stopButton" class="btn btn-secondary" disabled>Stop Camera</button>
                <button id="captureButton" class="btn btn-success" disabled>Capture Photo</button>
            </div>
            
            <div class="photo-preview">
                <h3>Current Profile Photo:</h3>
                <div id="currentPhoto">
                    <p>No photo taken yet</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Response Message -->
    <div id="responseMessage" class="response-message"></div>

    <script src="javascript.js"></script>
</body>
</html>