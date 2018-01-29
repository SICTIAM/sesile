import React from 'react'
import PropTypes from 'prop-types'
import { Route, Redirect } from 'react-router-dom'
import MenuBarAdmin from '../_components/MenuBarAdmin'

const AdminRoute = ({ component, exact = false, path, user, match, superAdmin = false }) => {
    return(
        user !== null &&
        <Route
            exact={exact}
            path={path}
            render={props => (
                user.roles.find(role => role.includes("ADMIN")) !== undefined && superAdmin === false ? 
                    (<div>
                        <MenuBarAdmin user={ user }/>
                        {React.createElement(component, {user, match})}
                    </div> ) :  
                    (user.roles.includes("ROLE_SUPER_ADMIN") ?
                        (<div>
                            <MenuBarAdmin user={ user }/>
                            {React.createElement(component, {user, match})}
                        </div>) :
                        (<Redirect to='/tableau-de-bord'/>))
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