import React from 'react'
import { render } from 'react-dom'
import App from './App'
import history from './_utils/History'
import {BrowserRouter as Router, Route, Link, Switch} from 'react-router-dom'

render((
    <Router history={history}>
        <App />
    </Router>
), document.getElementById('app'));