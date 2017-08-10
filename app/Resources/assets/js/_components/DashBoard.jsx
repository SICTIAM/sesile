import React, {Component} from 'react'

const styles = {
    progressbar: {
        width: '75%'
    }
}

class DashBoard extends Component {


    render () {
        return (
            <div className="grid-x grid-margin-x grid-padding-x align-center-middle">
                <div className="cell medium-5">
                    <div className="grid-x grid-padding-x panel">
                        <div className="cell medium-12 panel-heading">Mes derniers classeurs</div>
                        <div className="cell medium-12 panel-body">
                            <div className="grid-x grid-padding-x">
                                <div className="cell medium-4">Classeur numéros 213456</div>
                                <div className="cell medium-4">Date limite le 02/08/2017</div>
                                <div className="cell auto">ico 1</div>
                                <div className="cell auto">ico 2</div>
                                <div className="cell auto">ico 3</div>
                                <div className="cell auto">ico 4</div>
                                <div className="cell auto">ico 5</div>
                            </div>
                        </div>
                        <div className="cell medium-12 panel-body">
                            <div className="grid-x grid-padding-x">
                                <div className="cell medium-4">Classeur numéros 213456</div>
                                <div className="cell medium-4 text-justify">
                                    <span className="text-success">Date limite le <span className="text-bold">02/08/2017</span></span>
                                    <div className="success progress">
                                        <div className="progress-meter" style={styles.progressbar}></div>
                                    </div>
                                </div>
                                <div className="cell auto">ico 1</div>
                                <div className="cell auto">ico 2</div>
                                <div className="cell auto">ico 3</div>
                                <div className="cell auto">ico 4</div>
                                <div className="cell auto">ico 5</div>
                            </div>
                        </div>
                        <div className="cell medium-12 panel-body">
                            <div className="grid-x grid-padding-x">
                                <div className="cell medium-4">Classeur numéros 213456</div>
                                <div className="cell medium-4 text-justify">
                                    <span className="text-danger">Date limite le <span className="text-bold">02/08/2017</span></span>
                                    <div className="warning progress">
                                        <div className="progress-meter" style={styles.progressbar}></div>
                                    </div>
                                </div>
                                <div className="cell auto">ico 1</div>
                                <div className="cell auto">ico 2</div>
                                <div className="cell auto">ico 3</div>
                                <div className="cell auto">ico 4</div>
                                <div className="cell auto">ico 5</div>
                            </div>
                        </div>
                        <div className="cell medium-12 panel-body">
                            <div className="grid-x align-middle">
                                <div className="cell medium-4 text-bold">Classeur numéros 213456</div>
                                <div className="cell medium-4 text-justify">
                                    <span className="text-alert">Date limite le <span className="text-bold">02/08/2017</span></span>
                                    <div className="alert progress">
                                        <div className="progress-meter" style={styles.progressbar}></div>
                                    </div>
                                </div>
                                <div className="cell auto"><a href="#" className="btn-valid"></a></div>
                                <div className="cell auto"><a href="#" className="btn-sign"></a></div>
                                <div className="cell auto"><a href="#" className="btn-revert"></a></div>
                                <div className="cell auto"><a href="#" className="btn-refus"></a></div>
                                <div className="cell auto"><a href="#" className="btn-comment"></a></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="cell medium-5">
                    <div className="grid-x panel grid-padding-x">
                        <div className="cell medium-12 panel-heading">Mes derniers classeurs</div>
                        <div className="cell medium-12 panel-body">
                            <div className="grid-x grid-padding-x">
                                <div className="cell medium-4">Classeur numéros 213456</div>
                                <div className="cell medium-3">Date limite le 02/08/2017</div>
                                <div className="cell auto">ico 1</div>
                                <div className="cell auto">ico 2</div>
                                <div className="cell auto">ico 3</div>
                                <div className="cell auto">ico 4</div>
                                <div className="cell auto">ico 5</div>
                            </div>
                        </div>
                        <div className="cell medium-12 panel-body">
                            <div className="grid-x grid-padding-x">
                                <div className="cell medium-4">Classeur numéros 213456</div>
                                <div className="cell medium-3">Date limite le 02/08/2017</div>
                                <div className="cell auto">ico 1</div>
                                <div className="cell auto">ico 2</div>
                                <div className="cell auto">ico 3</div>
                                <div className="cell auto">ico 4</div>
                                <div className="cell auto">ico 5</div>
                            </div>
                        </div>
                        <div className="cell medium-12 panel-body">
                            <div className="grid-x grid-padding-x">
                                <div className="cell medium-4">Classeur numéros 213456</div>
                                <div className="cell medium-3">Date limite le 02/08/2017</div>
                                <div className="cell auto">ico 1</div>
                                <div className="cell auto">ico 2</div>
                                <div className="cell auto">ico 3</div>
                                <div className="cell auto">ico 4</div>
                                <div className="cell auto">ico 5</div>
                            </div>
                        </div>
                        <div className="cell medium-12 panel-body">
                            <div className="grid-x grid-padding-x">
                                <div className="cell medium-4">Classeur numéros 213456</div>
                                <div className="cell medium-3">Date limite le 02/08/2017</div>
                                <div className="cell auto">ico 1</div>
                                <div className="cell auto">ico 2</div>
                                <div className="cell auto">ico 3</div>
                                <div className="cell auto">ico 4</div>
                                <div className="cell auto">ico 5</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default DashBoard;