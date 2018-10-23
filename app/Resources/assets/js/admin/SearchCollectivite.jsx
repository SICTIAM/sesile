import React, { Component } from 'react'
import { translate } from 'react-i18next'
import {escapedValue} from "../_utils/Search"
import { object, func } from 'prop-types'


class SearchCollectivite extends Component {
    static contextTypes = {
        t: func
     }
    state = {
        collectivite: false,
        alotcall: false,
        search: '',
        collectivites: [],
        filteredCollectivites: [],
        filteredDisplay: []
    }

    componentWillReceiveProps(nextProps) {
        if (nextProps.collectivite.length > 0) {
            const collectivite = nextProps.collectivite
            this.setState({collectivites: nextProps.collectivite})
            this.setState({filteredCollectivites: nextProps.collectivite})
            this.isMoreThanFive(nextProps)
            this.setState({collectivite: true})
            this.setState({filteredDisplay: collectivite.slice(0, 5)})
        }
    }

    isMoreThanFive(nextProps) {
        if (nextProps.collectivite.length > 5) {
            this.setState({alotcall: true})
        }
    }

    handleSearch = (value) => {
        this.setState({search: value})
        const regex = escapedValue(value, this.state.filteredCollectivites, this.state.groups)
        const filteredCollectivites = this.state.collectivites.filter(collectivite => regex.test(collectivite.nom))
        this.setState({filteredCollectivites})
        this.setState({filteredDisplay: filteredCollectivites.slice(0, 5)})
    }

    render() {
        const { t } = this.context
        const collectiviteList = this.state.filteredDisplay.map((collectivite, key) => <li
            key={key + collectivite.id.toString()}
            style={{minWidth: '40%'}}>
            {this.props.classButton ?
                <a
                    href={Routing.generate("sesile_main_default_redirecttosubdomain", {subdomain: collectivite.domain})}
                    className={this.props.classButton}>{collectivite.nom}</a>
                :
                collectivite.nom
            }
            </li>
        )
        return (
            <ul className={this.props.className} style={{marginBottom:"0.5em"}}>
                {this.state.alotcall &&
                <li style={{listStyle:"none"}}>
                    <input
                        className="input-group-field"
                        placeholder={t('admin.collectivite.title')}
                        type="text"
                        value={this.state.search}
                        onChange={(e) => this.handleSearch(e.target.value)}
                        style={{margin:"0.5em", borderRadius:"5px", fontSize:"0.8em ", maxWidth:"400px"}}
                    />
                </li>
                }
                {this.state.collectivite && collectiviteList}
            </ul>
        )
    }
}

export default translate(['sesile'])(SearchCollectivite)