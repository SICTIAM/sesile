import React from 'react'
import { render } from 'react-dom'
import App from './App'
import history from './_utils/History'
import { Router } from 'react-router-dom'

render((
    <Router history={history}>
        <App />
    </Router>
), document.getElementById('app'));