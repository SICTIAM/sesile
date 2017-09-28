import React, { Component } from 'react'
import { Link } from 'react-router-dom'
import { I18nextProvider } from 'react-i18next'
import { Redirect } from 'react-router-dom'
import NotificationSystem from 'react-notification-system'
import PropTypes from 'prop-types'
import Login from './_components/Login'
import AppRoute from './_utils/AppRoute'
import i18n from './_utils/i18n'

class App extends Component {

    constructor() {
        super()
        this.state = {
            isAuthenticated: null
        }
        this._notificationSystem = null
    }

    static childContextTypes = {
        _addNotification: PropTypes.func,
        t: PropTypes.func,
    }

    notificationStyle = {
        Title: {
            DefaultStyle: {
                textTransform: 'uppercase'
            }
        },
        NotificationItem: { 
            DefaultStyle: { 
                margin: '100px 5px 2px 1px',
                backgroundColor: '#404257',
                color: 'white',
                fontWeight: '700'
            }
        },
        Dismiss: {
            DefaultStyle: {
              backgroundColor: '#404257'
            }
        }
      }

    getChildContext() {
        return {
            _addNotification: this._addNotification,
            t: this.t
        }
    }

    componentWillMount() {
        fetch(Routing.generate('sesile_user_userapi_isauthenticated'), { credentials: 'same-origin' })
            .then(response => response.json())
            .then(json => this.setState({isAuthenticated : json}))
    }

    componentDidMount() {
        $(document).foundation()
    }

    _addNotification = (notification) => {
        if (this._notificationSystem) {
            console.log(notification)
            this._notificationSystem.addNotification(notification)
        }
    }

    render () {
        const isAuthenticated = this.state.isAuthenticated
        return (
            <I18nextProvider i18n={i18n}>
                <div className="grid-x grid-y medium-grid-frame grid-frame">
                    <div className="cell header medium-cell-block-container cell-block-container">
                        <div className="grid-x align-middle align-center">
                            <div className="medium-1 cell">
                                <div className="logo text-center">
                                    <Link to={'/dashboard'}>
                                        <div className="logo-init">S</div>
                                        <div className="logo-name">sesile</div>
                                    </Link>
                                </div>
                            </div>
                            <div className="medium-5 cell">
                                <input type="search" name="sesile-search" className="sesile-search" id="" placeholder="Rechercher un classeur" />
                            </div>
                            <div className="medium-2 cell"></div>
                            <div className="medium-1 cell">
                                <div className="ico-notif-classeur">
                                    <span className="badge">5</span>
                                </div>
                            </div>
                            <div className="medium-1 cell">
                                <div className="ico-sign-classeur">
                                    <span>2</span>
                                </div>
                            </div>
                            <div className="medium-2 cell">
                                <Login/>
                            </div>
                        </div>
                    </div>
                    <div className="cell medium-auto medium-cell-block-container">
                        <div className="grid-x height100">
                            <div className="cell medium-1 medium-cell-block-y menu-left height100">
                                <div className="grid-y grid-padding-y height100 align-middle">
                                    <div className="cell auto">
                                        <a href="#"><span className="fa ico-hamburger"></span></a>
                                    </div>
                                    <div className="cell auto">
                                        <Link to={"/classeurs"}><span className="ico-new-classeur"></span></Link>
                                    </div>
                                    <div className="cell auto">
                                        <Link to={"/classeurs"}><span className="ico-sign-classeur"></span></Link>
                                    </div>
                                    <div className="cell auto">
                                        <a href="#"><span className="ico-valid-classeur"></span></a>
                                    </div>
                                    <div className="cell auto">
                                        <a href="#"><span className="ico-revert-classeur"></span></a>
                                    </div>
                                    <div className="cell auto">
                                        <a href="#"><span className="ico-refuse-classeur"></span></a>
                                    </div>
                                    <div className="cell auto">
                                        <a href="#"><span className="ico-notif"></span></a>
                                    </div>
                                    <div className="cell auto">
                                        <a href="#"><span className="ico-help"></span></a>
                                    </div>
                                </div>
                            </div>
                            <div className="cell medium-11 medium-cell-block-y main">
                                <div className="grid-x grid-padding-x">
                                    <div className="cell medium-12 actu align-center-middle text-center">
                                        <h1>Les dernières nouvelles</h1>
                                        <h2>Mise à jour du 12/07/2017</h2>
                                        <div className="cell medium-6">
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium adipisci distinctio dolorem dolorum illo incidunt ipsa libero mollitia, numquam officia, optio provident quaerat rerum tempore, veniam? Adipisci itaque iure maxime.</p>
                                        </div>
                                    </div>
                                    <div className="cell medium-12">
                                    <NotificationSystem ref={n => this._notificationSystem = n} style= {this.notificationStyle} />
                                        {isAuthenticated && <AppRoute isAuthenticated={isAuthenticated}/>}
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </I18nextProvider>
        )
    }
}

export default App