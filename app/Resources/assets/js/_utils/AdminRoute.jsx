import React from 'react'
import PropTypes from 'prop-types'
import { Route, Redirect } from 'react-router-dom'

const AdminRoute = ({ component, exact = false, path, user, match }) => {
    return(
        user !== null &&
        <Route
            exact={exact}
            path={path}
            render={props => (
                (user.roles.find(role => role.includes("ADMIN")) !== undefined) ? React.createElement(component, {user, match}) : <Redirect to={{ pathname: '/login',  state: {from: props.location}}}/>
            )}
        />
    )
}

const { object, bool, string, func } = PropTypes

AdminRoute.propTypes = {
    component: func.isRequired,
    exact: bool,
    path: string.isRequired,
    location: object,
    user: object.isRequired,
    match: object
}

export default AdminRoute