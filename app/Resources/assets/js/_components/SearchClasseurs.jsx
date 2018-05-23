import React, { Component } from 'react'
import { func } from 'prop-types'
import { escapedValue } from '../_utils/Search'
import { Link } from 'react-router-dom'
import { basicNotification } from "../_components/Notifications"
import { handleErrors } from '../_utils/Utils'
import { translate } from 'react-i18next'

class SearchClasseurs extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    state = {
        currentOrgId : this.props.user.current_org_id,
        classeurs: [],
        filteredClasseurs: [],
        nomClasseur: ''
    }

    componentDidMount () {
        this.fetchClasseurs()
    }

    fetchClasseurs() {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_classeur_classeurapi_listall', {orgId: this.state.currentOrgId}), { credentials: 'same-origin' })
            .then(handleErrors)
            .then(response => response.json())
            .then(classeurs => this.setState({classeurs}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                error.statusText)))
    }

    searchClasseurs = (e) => {
        const value = e.target.value
        const { classeurs, filteredClasseurs } = this.state
        if (value) {
            const regex = escapedValue(value, filteredClasseurs, classeurs)
            const filteredClasseurs = classeurs.filter(classeur => regex.test(classeur.nom))
            this.setState({filteredClasseurs})
        }
        this.setState({nomClasseur: value})
    }

    handleClick = (e) => {
        const value = e.target.value
        const classeur = this.state.classeurs.find(classeur => classeur.id === value)
        this.setState({
            filteredClasseurs: [],
            nomClasseur: classeur.nom
        })
    }

    handleBlur = () => {
        this.setState({filteredClasseurs: []})
    }

    render () {

        const { t } = this.context
        const { filteredClasseurs, nomClasseur } = this.state

        return (
            <div className="autocomplete input-group admin_search_input">

                <input className="input-group-field"
                       type="search"
                       name="sesile-search input-autocomplete"
                       placeholder={t('common.classeurs.search')}
                       value={ nomClasseur }
                       onChange={ this.searchClasseurs }
                       tabIndex="0"
                />
                <span className="input-group-label"><i className="fa fa-search"></i></span>

                {
                    filteredClasseurs.length > 0 &&
                    <ListClasseurs filteredClasseurs={ filteredClasseurs }
                                   handleClick={ this.handleClick }
                                   handleBlur={ this.handleBlur }
                    />
                }
            </div>
        )
    }
}

export default translate(['sesile'])(SearchClasseurs)

const ListClasseurs = ({filteredClasseurs, handleClick, handleBlur}) => {
    const options = filteredClasseurs.map((classeur, key) => {
        if(classeur && key < 10) {
            return (
                <Link to={`/classeur/${classeur.id}`} key={key} >
                    <li tabIndex={key} className={"list-group-item"} value={classeur.id} onClick={ handleClick } >
                        {classeur.nom}
                    </li>
                </Link>
            )
        }
    })
    return(
        <div className="list-autocomplete" onMouseLeave={ handleBlur } >
            <ul className="list-group">
                {options}
            </ul>
        </div>
    )
}