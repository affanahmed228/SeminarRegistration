// Simple Camera Registration Application
class CameraRegistrationApp {
    constructor() {
        this.video = document.getElementById('videoElement');
        this.canvas = document.getElementById('canvas');
        this.ctx = this.canvas.getContext('2d');
        this.stream = null;
        this.isCameraOn = false;
        this.currentPhoto = null;
        
        this.initializeApp();
    }
    
    initializeApp() {
        // Camera event listeners
        document.getElementById('startButton').addEventListener('click', () => this.startCamera());
        document.getElementById('stopButton').addEventListener('click', () => this.stopCamera());
        document.getElementById('captureButton').addEventListener('click', () => this.capturePhoto());
        
        // Form event listeners
        document.getElementById('registrationForm').addEventListener('submit', (e) => this.handleFormSubmit(e));
        document.getElementById('resetBtn').addEventListener('click', () => this.resetForm());
        
        this.updateButtonStates();
    }
    
    async startCamera() {
        try {
            this.stopCamera();
            
            console.log("Starting camera...");
            
            const constraints = {
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                },
                audio: false
            };
            
            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.video.srcObject = this.stream;
            
            this.video.onloadedmetadata = () => {
                this.video.play()
                    .then(() => {
                        this.isCameraOn = true;
                        this.updateButtonStates();
                        this.showMessage("✅ Camera started successfully!", "success");
                    })
                    .catch(error => {
                        console.error("Error playing video:", error);
                        this.showMessage("Error starting video: " + error.message, "error");
                    });
            };
            
        } catch (error) {
            console.error("Camera error:", error);
            this.showMessage("Camera error: " + error.message, "error");
        }
    }
    
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => {
                track.stop();
            });
            this.stream = null;
            this.video.srcObject = null;
            this.isCameraOn = false;
            this.updateButtonStates();
        }
    }
    
    capturePhoto() {
        if (!this.isCameraOn) {
            this.showMessage("Please start the camera first!", "error");
            return;
        }
        
        try {
            this.canvas.width = this.video.videoWidth;
            this.canvas.height = this.video.videoHeight;
            this.ctx.drawImage(this.video, 0, 0);
            
            this.currentPhoto = this.canvas.toDataURL('image/jpeg', 0.8);
            document.getElementById('photoData').value = this.currentPhoto;
            this.displayCurrentPhoto();
            
            this.showMessage("✅ Photo captured!", "success");
            
        } catch (error) {
            console.error("Capture error:", error);
            this.showMessage("Error capturing photo", "error");
        }
    }
    
    displayCurrentPhoto() {
        const container = document.getElementById('currentPhoto');
        container.innerHTML = this.currentPhoto ? 
            `<img src="${this.currentPhoto}" alt="Profile Photo" style="max-width: 300px; border: 2px solid green;">` : 
            '<p>No photo taken yet</p>';
    }
    
    async handleFormSubmit(e) {
        e.preventDefault();
        console.log('Form submitted');
        
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Registering...';
        submitBtn.disabled = true;
        
        try {
            // Create FormData from the form
            const formData = new FormData(document.getElementById('registrationForm'));
            
            console.log('Sending form data to register.php...');
            
            const response = await fetch('register.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            console.log('Server response:', result);
            
            if (result.success) {
                this.showMessage("✅ " + result.message, "success");
                this.resetForm();
            } else {
                this.showMessage("❌ " + result.message, "error");
            }
            
        } catch (error) {
            console.error('Submit error:', error);
            this.showMessage('❌ Registration failed: ' + error.message, "error");
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }
    
    resetForm() {
        document.getElementById('registrationForm').reset();
        this.currentPhoto = null;
        document.getElementById('currentPhoto').innerHTML = '<p>No photo taken yet</p>';
        document.getElementById('photoData').value = '';
        this.hideMessage();
        this.stopCamera();
    }
    
    updateButtonStates() {
        document.getElementById('startButton').disabled = this.isCameraOn;
        document.getElementById('stopButton').disabled = !this.isCameraOn;
        document.getElementById('captureButton').disabled = !this.isCameraOn;
    }
    
    showMessage(message, type) {
        const element = document.getElementById('responseMessage');
        element.textContent = message;
        element.className = `response-message ${type}`;
        element.style.display = 'block';
    }
    
    hideMessage() {
        document.getElementById('responseMessage').style.display = 'none';
    }
}

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    window.cameraApp = new CameraRegistrationApp();
});