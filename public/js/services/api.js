/**
 * API utility service for making authenticated requests to the backend
 */
export async function fetchApi(endpoint, method = 'GET', body = null, callback = null) {
    const token = localStorage.getItem('token');
    try {
        const response = await fetch(endpoint, {
            method,
            body,
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            const error = await response.text();
            pageState.setPageState('error', { error });
            return;
        }

        let data;
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            data = { error: await response.text() };
        } else {
            data = await response.json();
        }

        const stateName = Object.keys(data)[0];
        if (callback) {
            callback(stateName, data[stateName]);
        }

        return data; // Return the data for further processing if needed
    } catch (error) {
        console.error('Error en la llamada API:', error);
        document.getElementById('gamesContainer').innerHTML = error;
    }
}
