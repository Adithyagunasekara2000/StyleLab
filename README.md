
# Online Clothing Shop  - StyleLab

## Overview  
This is an online clothing shop developed using PHP. The website allows users to browse various collections, including Men’s, Women’s, Kids’, and Bags. It also features seasonal offers based on major festivals and events.  

## Features  
- **Home Page:** Displays featured collections and seasonal offers.  
- **Dynamic Seasonal Offers:** Special collections appear automatically based on the current date.  
- **User-Friendly Navigation:** Responsive design with a smooth browsing experience.  
- **Secure Data Handling:** Uses `htmlspecialchars()` to prevent XSS attacks.  

## Installation  
1. Clone or download the repository.  
2. Ensure you have a local server environment like XAMPP, WAMP, or MAMP installed.  
3. Place the project folder in the `htdocs` directory (for XAMPP).  
4. Start Apache and MySQL from your server control panel.  
5. Open the project in a browser by navigating to `http://localhost/your_project_folder/`.  


## Seasonal Offer Logic  
The seasonal offers are determined by PHP functions that check the current date and display relevant collections. The logic includes:  
- Sinhala & Tamil New Year: April 1 - April 15  
- Vesak Festival: May 1 - May 31  
- Deepavali: November 1 - November 30  
- Christmas: December 1 - December 31  

If no seasonal offers are active, a default "Classic Collection" is displayed.  

## Future Improvements  
- Implement user authentication (Login & Register).  
- Add a shopping cart and checkout functionality.  
- Integrate a payment gateway for seamless transactions.  
- Enhance the UI with more animations and interactive elements.  

![Screenshot 2025-03-26 233038](https://github.com/user-attachments/assets/edb0d923-083a-4f6b-a26a-7cf488b3e98b)
