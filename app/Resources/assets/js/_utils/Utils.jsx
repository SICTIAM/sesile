const handleErrors = (response) => {
    if(response.status >= 200 && response.status < 300) return response
    else throw response
}

export { handleErrors }