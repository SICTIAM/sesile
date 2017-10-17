import React, { Component } from 'react'
import { Link } from 'react-router-dom'
import UserAvatar from 'react-user-avatar'

class MenuBar extends Component {


    constructor(props) {
        super(props);
        this.state = {
            user_is_auth: false,
            user: {
                collectivite: {}
            }
        };
    }

    componentDidMount() {
        fetch(Routing.generate('sesile_user_userapi_isauthenticated'), { credentials: 'same-origin' })
            .then(response => response.json())
            .then(json => {
                this.setState({user_is_auth: json})
                json && this.fetchUser()
            })

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

        const { user_is_auth, user } = this.state

        return (

            <div className="user-log" data-toggle="user-infos">
                {
                    (user_is_auth && user.id) ?
                    <div>
                        <div className="grid-x grid-padding-x row align-middle">
                            <div className="medium-4 cell shrink">
                                {
                                    user.path ?
                                        <UserAvatar size="48" name={user._prenom} src={"/uploads/avatars/" + user.path} />
                                        : <UserAvatar size="48" name={user._prenom} className="txt-avatar" />
                                }
                            </div>
                            <div className="medium-8 cell">
                                <div className="grid-x">
                                    <div className="medium-10 cell">
                                        <div className="grid-x">
                                            <div className="columns user-name">{ user._prenom + ' ' + user._nom }</div>
                                        </div>
                                        <div className="grid-x">
                                            <div className="columns user-company">{ user.collectivite.nom }</div>
                                        </div>
                                    </div>
                                    <div className="medium-2 cell">
                                        <button className="button arrow-down" type="button">&nbsp;</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="dropdown-pane" data-position="bottom" data-alignment="right" id="user-infos" data-dropdown data-auto-focus="true">
                            <div className="grid-x grid-padding-x row align-middle">
                                <div className="medium-4 cell">
                                    <div className="avatar text-center">S</div>
                                </div>
                                <div className="medium-8 cell">
                                    <div className="grid-x">
                                        <div className="medium-12 cell">
                                            <div className="grid-x">
                                                <div className="columns user-name">{ user._prenom + ' ' + user._nom }</div>
                                            </div>
                                            <div className="grid-x">
                                                <div className="columns user-mail">{ user.email }</div>
                                            </div>
                                            <div className="grid-x">
                                                <div className="columns user-company">{ user.collectivite.nom }</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="grid-x">
                                <div className="medium-6 cell">
                                    <Link to={"/utilisateur/mon-compte"} className="button primary btn-user-conf">paramètre du compte</Link>
                                </div>
                                <div className="medium-6 cell text-right">
                                    <a href="/logout" className="button secondary btn-user-logout">Déconnexion</a>
                                </div>
                            </div>
                            <hr/>
                            <div className="grid-x">
                                <div className="medium-7 cell">
                                    <a href="#" className="button gray btn-user-conf">changer de compte</a>
                                </div>
                                <div className="medium-5 cell text-right">
                                    <Link to={"/admin/circuits-de-validation"} className="button gray btn-user-conf">admin</Link>
                                </div>
                            </div>

                        </div>
                    </div>
                    :
                    <a href="/dashboard" className="button primary">Connexion</a>
                }
            </div>
        )
    }
}

export default MenuBar