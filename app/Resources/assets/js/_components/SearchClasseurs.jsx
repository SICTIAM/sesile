import React, { Component } from 'react'
import { func } from 'prop-types'
import { Link } from 'react-router-dom'
import { handleErrors } from '../_utils/Utils'
import { translate } from 'react-i18next'
import Debounce from 'debounce'

class SearchClasseurs extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    state = {
        currentOrgId : this.props.user.current_org_id,
        classeurs: [],
        filteredClasseurs: [],
        nomClasseur: '',
        loading: false,
        message: null
    }

    fetchClasseurs = Debounce(() => {
        const { t } = this.context
        this.setState({loading: true, message: "Chargement ..."})
        fetch(Routing.generate('sesile_classeur_classeurapi_searchclasseurs', {orgId: this.state.currentOrgId}), {
            method: 'POST',
            body: JSON.stringify({name: this.state.nomClasseur}),
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin' })
            .then(handleErrors)
            .then(response => response.json())
            .then(filteredClasseurs => {
                this.setState({loading: false})
                if(filteredClasseurs <= 0) this.setState({message: t('common.no_result_globel_classeur_research')})
                else this.setState({filteredClasseurs})
            })
            .catch(() => {
                this.setState({message: t('common.error_global_classeur_research')})
            })
    }, 800)

    searchClasseurs = (e) => {
        const value = e.target.value
        this.setState({nomClasseur: value})

        if (value.length >= 3) {
            this.fetchClasseurs()
        } else this.setState({filteredClasseurs: [], message: null})
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
        this.setState({filteredClasseurs: [], message: null})
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
                       tabIndex="0"/>
                <span className="input-group-label">
                    <i className="fa fa-search"/>
                </span>
                {filteredClasseurs.length > 0 ?
                    <ListClasseurs
                        filteredClasseurs={ filteredClasseurs }
                        handleClick={ this.handleClick }
                        handleBlur={ this.handleBlur }/> :
                        this.state.message !== null &&
                            <div className="list-autocomplete" onMouseLeave={() => this.handleBlur() } >
                                <ul className="list-group">
                                    <li className={"list-group-item"}>
                                        {this.state.message}
                                    </li>
                                </ul>
                            </div>}
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