import React, { Component } from 'react'
import { Link } from 'react-router-dom'
import { func } from 'prop-types'
import UserAvatar from 'react-user-avatar'
import { translate } from 'react-i18next'

class MenuBar extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
        this.state = {
            user: {
                collectivite: {}
            }
        }
    }

    componentWillReceiveProps(nextProps) {
        this.setState({user: nextProps.user})

    }

    componentDidUpdate() {
        $('.user-log').foundation()
    }

    render(){

        const { user } = this.state
        const { t } = this.context

        return (

            <div className="user-log" data-toggle="user-infos">
                {
                    user.id ?
                        <ul className="dropdown menu align-center" data-dropdown-menu>
                            <li>
                                <a href="#" className="button primary hollow user-complete-name">
                                    <div className="grid-x align-middle">
                                        {
                                            user.path ?
                                                <UserAvatar size="20" name={user._prenom} src={"/uploads/avatars/" + user.path} />
                                                : <UserAvatar size="20" name={user._prenom} className="txt-avatar" />
                                        }
                                        &nbsp; { user._prenom + ' ' + user._nom }
                                    </div>
                                </a>
                                <ul className="menu">
                                    <li>
                                        <Link to="/utilisateur/mon-compte" className="button secondary clear">
                                            <i className="fa fa-user-circle"></i>
                                            { t('common.menu.account_parameter') }
                                        </Link>
                                    </li>
                                    <hr/>
                                    {(user.roles.find(role => role.includes("ADMIN"))) &&
                                        <div>
                                            <li>
                                                <Link to="/admin/circuits-de-validation" className="button secondary clear">
                                                    <i className="fa fa-cogs"></i>
                                                    { t('common.menu.admin') }
                                                </Link>
                                            </li>
                                            <hr/>
                                        </div>}
                                    <li>
                                        <a href="/logout" className="button secondary clear">
                                            <i className="fa fa-sign-out"></i>
                                            { t('common.menu.disconnection') }
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    :
                    <a href="/connect/ozwillo" className="button primary hollow">{ t('common.menu.connection') }</a>
                }
            </div>
        )
    }
}

export default translate(['sesile'])(MenuBar)

MenuBar.contextTypes = {
    t: func
}