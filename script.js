const defaultCity = "Luton";
async function fetchWeather(cityName) {
    try {
        const response = await fetch(`weather.php?q=${cityName}`);
        if (!response.ok) {
            throw new Error("Error while fetching data");
        }
        const data = await response.json();
        console.log("Fetched data:", data);
        updateWeather(data);
        saveToLocalStorage(cityName, data);
        updateSearchedCities();
    } catch (err) {
        console.log("Error has occurred: " + err);
    }
}
function updateWeather(data) {
    if (data && data.length > 0) {
        const weather = data[0];
        if (cityElement && date && Icon && weather_description && temperature && pressure && windSpeed && humidity) {
            cityElement.textContent = weather.city;
            const currentDate = new Date(weather.created_at);
            date.textContent = currentDate.toDateString();
            Icon.src = `https://openweathermap.org/img/wn/${weather.icon_code}@2x.png`;
            weather_description.textContent = weather.weather_description;
            temperature.textContent = `${weather.temperature}°C`;
            pressure.textContent = `${weather.pressure} hPa`;
            windSpeed.textContent = `${weather.wind} km/h`;
            humidity.textContent = `${weather.humidity}%`;
        } else {
            console.log("Some DOM elements are missing.");
        }
    }
}
function saveToLocalStorage(cityName, data) {
    const storedData = JSON.parse(localStorage.getItem('weatherData')) || {};
    storedData[cityName] = data;
    localStorage.setItem('weatherData', JSON.stringify(storedData));
}
function loadFromLocalStorage(cityName) {
    const storedData = JSON.parse(localStorage.getItem('weatherData')) || {};
    return storedData[cityName] || null;
}
function updateSearchedCities() {
    const storedData = JSON.parse(localStorage.getItem('weatherData')) || {};
    const cityListElement = document.querySelector(".searched-cities");
    if (cityListElement) {
        cityListElement.innerHTML = '';
        for (const city in storedData) {
            const cityElement = document.createElement('div');
            cityElement.textContent = city;
            cityElement.classList.add('searched-city');
            cityElement.addEventListener('click', () => {
                const data = loadFromLocalStorage(city);
                updateWeather(data);
            });
            cityListElement.appendChild(cityElement);
        }
    }
}
const cityElement = document.querySelector(".city");
const temperature = document.querySelector(".temp");
const pressure = document.querySelector(".pressure");
const windSpeed = document.querySelector(".wind");
const humidity = document.querySelector(".humidity");
const date = document.querySelector(".date-time");
const Icon = document.querySelector(".weather-icon");
const weather_description = document.querySelector(".weather-condition");
const searchButton = document.querySelector("button");
const input = document.querySelector("input");
searchButton.addEventListener("click", () => {
    const cityName = input.value;
    if (cityName !== "") {
        fetchWeather(cityName);
        input.value = "";
    }
});
document.addEventListener("DOMContentLoaded", () => {
    const data = loadFromLocalStorage(defaultCity);
    if (data) {
        updateWeather(data);
    } else {
        fetchWeather(defaultCity);
    }
    updateSearchedCities();
});
