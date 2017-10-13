const handleErrors = (response) => {
    if(response.ok) return response
    else throw response
}

export { handleErrors }