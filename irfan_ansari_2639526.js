let temperature = document.getElementById("temperature");
let weatherCondition = document.getElementById("weathercondition");
let mainWeather = document.getElementById("mainweather");
let cityDisplay = document.getElementById("city");
let dateDisplay = document.getElementById("date");
let dayDisplay = document.getElementById("day");
// let sunriseDisplay = document.getElementById("sunrise");
// let sunsetDisplay = document.getElementById("sunset");
// let longitudeDisplay = document.getElementById("longitude");
// let latitudeDisplay = document.getElementById("latitude");
let iconDisplay = document.getElementById("icon");
let button = document.getElementById("searchbtn");
let searchBar = document.getElementById("searchbar");

let detailBoxes = document.getElementsByClassName("detail-box");
let pressureDetail = detailBoxes[0];
let humidityDetail = detailBoxes[1];
let windDetail = detailBoxes[2];
let directionDetail = detailBoxes[3];

button.addEventListener("click", function () {
    weatherData(searchBar.value);
});

async function weatherData(city) {
    try {

        let data;

        // Check online status
        if (navigator.onLine) {

            // Online → Fetch API data
            let response = await fetch(
                "https://api.openweathermap.org/data/2.5/weather?q=" 
                + city +
                "&appid=141870e68735c1cc4123120497a35c7a&units=metric"
            );

            if (!response.ok) {
                throw new Error("City not found");
            }

            data = await response.json();

            // Save to Local Storage
            localStorage.setItem(city, JSON.stringify(data));

            console.log("Data fetched from API");

        } else {

            // Offline → Get stored data
            let storedData = localStorage.getItem(city);

            if (!storedData) {
                throw new Error(
                    "No cached weather data available for this city"
                );
            }

            data = JSON.parse(storedData);

            console.log("Data loaded from Local Storage");
        }


        // Display weather information

        temperature.textContent = data.main.temp + "°C";
        weatherCondition.textContent = data.weather[0].main;
        mainWeather.textContent = data.weather[0].description;
        cityDisplay.textContent = data.name;

        let currentDate = new Date();

        dateDisplay.textContent = "Date: " + currentDate.toLocaleDateString();
        dayDisplay.textContent = "Day: " + currentDate.toLocaleDateString(undefined, {weekday:"long"});

        sunriseDisplay.textContent = "Sunrise: " + new Date( data.sys.sunrise * 1000).toLocaleTimeString();

        sunsetDisplay.textContent = "Sunset: " + new Date(data.sys.sunset * 1000 ).toLocaleTimeString();

        longitudeDisplay.textContent = "Longitude: " + data.coord.lon;

        latitudeDisplay.textContent = "Latitude: " + data.coord.lat;

        // Weather icon
        if (data.weather[0].icon) {
            iconDisplay.src = "https://openweathermap.org/img/wn/" + data.weather[0].icon + "@2x.png";

            iconDisplay.alt = data.weather[0].description;
        }

        pressureDetail.querySelector("p").textContent = "Pressure: " + data.main.pressure + " hPa";

        humidityDetail.querySelector("p").textContent = "Humidity: " + data.main.humidity +"%";

        windDetail.querySelector("p").textContent = "Wind Speed: " + data.wind.speed + " m/s";

        directionDetail.querySelector("p").textContent ="Direction: " + data.wind.deg +"°";

    }

    catch(error) {
        alert(error.message);
    }
}

// Default city
weatherData("Scottsboro");