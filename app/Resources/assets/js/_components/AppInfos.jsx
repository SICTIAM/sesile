import React, { Component } from 'react'
import { Link } from 'react-router-dom'
import { func } from 'prop-types'
import { translate } from 'react-i18next'

class AppInfos extends Component {
    
    static contextTypes = {
        t: func
    }

    state = {
        informations: {
            version: 0,
            contact_link: ''
        }
    }

    componentDidMount() {
        fetch(Routing.generate('sesile_main_default_getappinformation'), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => this.setState({informations: json}))
    }
    
    render() {
        const { t } = this.context
        return (
            <div className=" cell auto app-infos text-uppercase">
                <div className="">
                    <a href={this.state.informations.contact_link}>{t('footer.contact')}</a>
                </div>
                <div className="">
                        <Link to="">{t('footer.general_conditions')}</Link>
                </div>
                <div className="">
                        <Link to="/documentations">{t('footer.version', {version: this.state.informations.version})}</Link>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(AppInfos)