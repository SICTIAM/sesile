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
        super(props);
        this.state = {
            user: {
                collectivite: {}
            }
        };
    }

    componentDidMount() {
        this.fetchUser()
    }

    fetchUser() {
        fetch(Routing.generate("sesile_user_userapi_getcurrent"), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({user: json})
                $('.user-log').foundation()
            })
    }

    render(){

        const { user } = this.state
        const { t } = this.context

        return (

            <div className="user-log" data-toggle="user-infos">
                {
                    user.id ?
                        <ul className="dropdown menu" data-dropdown-menu>
                            <li>
                                <a href="#" className="button primary hollow">
                                    <div className="grid-x align-middle">
                                        <div className="cell medium-3 align-center">
                                            {
                                                user.path ?
                                                    <UserAvatar size="30" name={user._prenom} src={"/uploads/avatars/" + user.path} />
                                                    : <UserAvatar size="30" name={user._prenom} className="txt-avatar" />
                                            }
                                        </div>
                                        <div className="cell medium-9">
                                            { user._prenom + ' ' + user._nom }
                                        </div>
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
                                    <li>
                                        <Link to="/admin/circuits-de-validation" className="button secondary clear">
                                            <i className="fa fa-cogs"></i>
                                            { t('common.menu.admin') }
                                        </Link>
                                    </li>
                                    <hr/>
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
                    <a href="/login" className="button primary hollow">{ t('connection') }</a>
                }
            </div>
        )
    }
}

export default translate(['sesile'])(MenuBar)

MenuBar.contextTypes = {
    t: func
}