import React, { Component } from 'react'
import Moment from 'moment';
import PropTypes from 'prop-types'
import { Link } from 'react-router-dom'
import UserAvatar from 'react-user-avatar'
import CircuitListClasseur from '../circuit/CircuitListClasseur'


const styles = {
    progressbar: {
        width: '75%'
    }
}

class ClasseursRow extends Component {


    constructor(props) {
        super(props);
        this.state = {classeurs: null};
    }

    componentDidMount() {

    }

    render(){
        Moment.locale('fr')
        const classeur = this.props.classeur

        const today = Moment()
        const validation = Moment(classeur.validation)
        const diff = validation.diff(today)

        console.log(diff)

        return (

            <div id={classeur.id} className="grid-x grid-padding-x grid-padding-y classeur align-middle">
                <div className="cell medium-2">
                    <div className="grid-x align-center-middle">
                        <div className="cell medium-3">
                            {
                                classeur.user.path ?
                                    <UserAvatar size="48" name={classeur.user._prenom} src={"/uploads/avatars/" + classeur.user.path} />
                                    : <UserAvatar size="48" name={classeur.user._prenom} className="txt-avatar" />
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
                    <span className="text-alert">Date limite le <span className="text-bold">{Moment(classeur.validation).format('L')}</span></span>
                    <div className="alert progress">
                        <div className="progress-meter" style={styles.progressbar}></div>
                    </div>
                </div>
                <CircuitListClasseur classeurId={classeur.id} user={classeur.user} />

                <div className="cell medium-2">
                    <div className="grid-x">
                        <div className="cell auto"><a href="#" className="btn-valid"></a></div>
                        <div className="cell auto"><a href="#" className="btn-sign"></a></div>
                        <div className="cell auto"><a href="#" className="btn-revert"></a></div>
                        <div className="cell auto"><a href="#" className="btn-refus"></a></div>
                        <div className="cell auto"><a href="#" className="btn-comment"></a></div>
                    </div>
                </div>
                <div className="cell medium-1 text-center">
                    <input type="checkbox" id={classeur.id} />
                </div>
            </div>

        )
    }
}

ClasseursRow.PropTypes = {
    classeur: PropTypes.object.isRequired
}

export default ClasseursRow