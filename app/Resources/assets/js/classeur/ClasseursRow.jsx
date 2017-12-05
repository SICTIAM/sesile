import React, { Component } from 'react'
import Moment from 'moment';
import PropTypes from 'prop-types'
import { Link } from 'react-router-dom'
import { Avatar } from '../_components/Form'
import CircuitListClasseur from '../circuit/CircuitListClasseur'
import ClasseursButtonList from "./ClasseursButtonList"

class ClasseursRow extends Component {

    render(){
        Moment.locale('fr')
        const classeur = this.props.classeur
        const creation = Moment(classeur.creation)
        const validation = Moment(classeur.validation)
        const diffToday = validation.diff(Moment(), 'days')
        const diffToCreation = Moment().diff(creation, 'days')

        let classProgress, percentProgress

        if (diffToday < 0) {
            classProgress = "alert"
            percentProgress = 100
        } else if (diffToday < 1) {
            classProgress = "warning"
            percentProgress = 100
        } else {
            classProgress = "success"
            percentProgress = 100 - diffToday / diffToCreation * 100
        }


        return (

            <div id={classeur.id} className="grid-x grid-padding-x grid-padding-y classeur align-middle">
                <div className="cell medium-2">
                    <div className="grid-x grid-margin-x align-center-middle">
                        <div className="cell medium-3">
                            {classeur.user.path ?
                                <Avatar size="48" nom={classeur.user._nom} src={"/uploads/avatars/" + classeur.user.path}/> :
                                <Avatar size="48" nom={classeur.user._nom}/>
                            }
                        </div>
                        <div className="cell medium-9">
                            <div className="text-bold">{classeur.user._prenom} {classeur.user._nom}</div>
                        </div>
                    </div>

                </div>
                <div className="cell medium-3">
                    <Link to={`/classeur/${classeur.id}`}><span className="text-bold">{classeur.nom}</span></Link>
                </div>
                <div className="cell medium-2">Déposé le <span className="text-bold">{Moment(classeur.creation).format('L')}</span></div>
                <div className="cell medium-2" data-toggle={"example-dropdown-" + classeur.id}>
                    <span className={"text-" + classProgress}>Date limite le <span className="text-bold">{Moment(classeur.validation).format('L')}</span></span>
                    <div className={classProgress +" progress"}>
                        <div className="progress-meter" style={{width: percentProgress + '%'}}></div>
                    </div>
                </div>
                <CircuitListClasseur classeurId={classeur.id} user={classeur.user} />

                <div className="cell medium-2">
                    <ClasseursButtonList classeur={classeur} />
                </div>
                <div className="cell medium-1 text-center">
                    <input type="checkbox" id={classeur.id} checked={classeur.checked || false} onChange={this.props.checkClasseur} className="checkClasseur" />
                </div>
            </div>

        )
    }
}

ClasseursRow.PropTypes = {
    classeur: PropTypes.object.isRequired,
    checkClasseur: PropTypes.func.isRequired
}

export default ClasseursRow